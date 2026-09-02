<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfileController extends Controller
{
    /**
     * POST /api/profile/complete — multi-step profile setup submission
     */
    public function complete(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'phone' => 'required|string|min:10|max:20',
            'sohni_id_type' => 'required|in:free,premium',
            'address' => 'required|string|min:3|max:255',
            'about_me' => 'nullable|string|max:2000',
            'experiences' => 'nullable|json',
            'educations' => 'nullable|json',
            'profile_pic' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
        ]);

        // Generate a guaranteed-unique Sohni ID server-side
        $sohniId = $user->sohni_id ?? $this->generateSohniId($data['sohni_id_type']);

        $avatarPath = $user->avatar_path;
        if ($request->hasFile('profile_pic')) {
            $avatarPath = $request->file('profile_pic')->store('avatars', 'public');
        }

        $user->forceFill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => $data['first_name'] . ' ' . $data['last_name'],
            'phone' => $data['phone'],
            'sohni_id' => $sohniId,
            'sohni_id_type' => $data['sohni_id_type'],
            'address' => $data['address'],
            'about_me' => $data['about_me'] ?? null,
            'experiences' => $this->cleanExperiences($data['experiences'] ?? null),
            'avatar_path' => $avatarPath,
            'profile_completed_at' => now(),
        ])->save();

        // Save degrees (multiple) — each stored encrypted at rest
        $degrees = collect(json_decode($data['educations'] ?? '[]', true) ?: [])
            ->filter(fn ($d) => ! empty($d['title']))
            ->take(10);

        $user->educations()->delete();
        foreach ($degrees as $degree) {
            $user->educations()->create([
                'title' => substr((string) $degree['title'], 0, 150),
                'completion_date' => substr((string) ($degree['completion_date'] ?? ''), 0, 30),
                'grade' => substr((string) ($degree['grade'] ?? ''), 0, 50),
                'marks' => substr((string) ($degree['marks'] ?? ''), 0, 50),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile created successfully.',
            'data' => [
                'user' => $user->fresh()->load('educations'),
                'redirect' => '/dashboard',
            ],
        ], 201);
    }

    /**
     * GET /api/profile — current user profile
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('educations');
        $joinDate = $user->created_at;
        $now = now();

        // Calculate member duration in detail format
        $totalDays = $joinDate->diffInDays($now);
        
        if ($totalDays < 1) {
            $duration = 'Today';
        } else {
            $years = intdiv($totalDays, 365);
            $remainingDays = $totalDays % 365;
            $months = intdiv($remainingDays, 30);
            $days = $remainingDays % 30;
            
            $parts = [];
            if ($years > 0) {
                $parts[] = $years . ' year' . ($years > 1 ? 's' : '');
            }
            if ($months > 0) {
                $parts[] = $months . ' month' . ($months > 1 ? 's' : '');
            }
            if ($days > 0 || empty($parts)) {
                $parts[] = $days . ' day' . ($days !== 1 ? 's' : '');
            }
            
            $duration = implode(' ', $parts);
        }

        return response()->json([
            'success' => true,
            'data' => array_merge($user->toArray(), [
                'avatar_url' => $user->avatar_path ? '/storage/' . $user->avatar_path : null,
                'cover_url' => $user->cover_path ? '/storage/' . $user->cover_path : null,
                'member_since' => $user->created_at->format('F Y'),
                'member_duration' => $duration,
                'friends_count' => $user->friends_count ?? 0,
                'followers_count' => $user->followers_count ?? 0,
                'groups_count' => $user->groups_count ?? 0,
                'new_friends_this_week' => $user->new_friends_this_week ?? 0,
            ]),
        ]);
    }

    /**
     * GET /profile/download — download the authenticated user's profile as a PDF
     */
    public function downloadProfile(Request $request)
    {
        $user = $request->user()->load('educations');
        $avatarData = null;

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            $mime = Storage::disk('public')->mimeType($user->avatar_path) ?: 'image/jpeg';
            $avatarData = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($user->avatar_path));
        }

        $pdf = Pdf::loadView('profile-download', [
            'user' => $user,
            'memberSince' => $user->created_at?->format('F Y'),
            'avatarData' => $avatarData,
        ])->setPaper('a4', 'portrait');

        $filename = 'sohni-' . Str::slug($user->name ?: 'profile') . '-profile.pdf';

        return $pdf->download($filename);
    }

    /**
     * POST /api/profile/update — update user profile (name, phone, address, cover image, educations)
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => 'nullable|string|min:2|max:50',
            'last_name' => 'nullable|string|min:2|max:50',
            'phone' => 'nullable|string|min:10|max:20',
            'address' => 'nullable|string|min:3|max:255',
            'about_me' => 'nullable|string|max:2000',
            'experiences' => 'nullable|json',
            'cover_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
            'profile_pic' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
            'educations' => 'nullable|json',
        ]);

        // Update basic profile info if provided
        $updates = [];
        if (isset($data['first_name'])) {
            $updates['first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $updates['last_name'] = $data['last_name'];
        }
        if (isset($data['first_name']) || isset($data['last_name'])) {
            $updates['name'] = ($data['first_name'] ?? $user->first_name) . ' ' . ($data['last_name'] ?? $user->last_name);
        }
        if (isset($data['phone'])) {
            $updates['phone'] = $data['phone'];
        }
        if (isset($data['address'])) {
            $updates['address'] = $data['address'];
        }
        if (array_key_exists('about_me', $data)) {
            $updates['about_me'] = $data['about_me'];
        }
        if (array_key_exists('experiences', $data)) {
            $updates['experiences'] = $this->cleanExperiences($data['experiences']);
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $updates['cover_path'] = $request->file('cover_image')->store('covers', 'public');
        }

        // Handle avatar/profile picture upload
        if ($request->hasFile('profile_pic')) {
            $updates['avatar_path'] = $request->file('profile_pic')->store('avatars', 'public');
        }

        // Update user
        if (!empty($updates)) {
            $user->forceFill($updates)->save();
        }

        // Update educations if provided
        if (isset($data['educations'])) {
            $degrees = collect(json_decode($data['educations'], true) ?: [])
                ->filter(fn ($d) => !empty($d['title']))
                ->take(10);

            $user->educations()->delete();
            foreach ($degrees as $degree) {
                $user->educations()->create([
                    'title' => substr((string) $degree['title'], 0, 150),
                    'completion_date' => substr((string) ($degree['completion_date'] ?? ''), 0, 30),
                    'grade' => substr((string) ($degree['grade'] ?? ''), 0, 50),
                    'marks' => substr((string) ($degree['marks'] ?? ''), 0, 50),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user->fresh()->load('educations')->toArray() + [
                'avatar_url' => $user->avatar_path ? '/storage/' . $user->avatar_path : null,
                'cover_url' => $user->cover_path ? '/storage/' . $user->cover_path : null,
                'member_since' => $user->created_at->format('F Y'),
            ],
        ]);
    }

    /**
     * GET /api/sohni-ids/generate?type=free|premium — preview an ID
     */
    public function generateId(Request $request)
    {
        $type = $request->query('type', 'free');

        return response()->json([
            'success' => true,
            'data' => [
                'sohni_id' => $this->generateSohniId($type === 'premium' ? 'premium' : 'free'),
                'type' => $type,
            ],
        ]);
    }

    private function generateSohniId(string $type): string
    {
        do {
            $id = (string) random_int(10000000000000, 99999999999999);
        } while (User::where('sohni_id', $id)->exists());

        return $id;
    }

    private function cleanExperiences(?string $experiences): array
    {
        $items = json_decode($experiences ?: '[]', true);

        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(fn ($item) => is_array($item) && ! empty(trim((string) ($item['title'] ?? ''))))
            ->take(10)
            ->map(fn ($item) => [
                'title' => substr(trim((string) ($item['title'] ?? '')), 0, 150),
                'company' => substr(trim((string) ($item['company'] ?? '')), 0, 150),
                'start_date' => substr(trim((string) ($item['start_date'] ?? '')), 0, 30),
                'end_date' => substr(trim((string) ($item['end_date'] ?? '')), 0, 30),
                'description' => substr(trim((string) ($item['description'] ?? '')), 0, 1000),
            ])->values()->all();
    }

    /**
     * GET /api/friends/recent — get CURRENT LOGGED-IN user's recent friends only
     * IMPORTANT: Always returns data only for Auth::user(), never for other users
     */
    public function recentFriends(Request $request)
    {
        $currentUser = $request->user(); // Only authenticated user
        
        // TODO: Query friend relationships for $currentUser when friends system is built
        // Query should: WHERE user_id = $currentUser->id, ORDER BY created_at DESC, LIMIT 20
        // Return: [{ id, name, avatar_url, sohni_id_type }, ...]
        
        return response()->json([
            'success' => true,
            'data' => [], // Empty until friends system implemented
        ]);
    }

    /**
     * GET /api/friends/accepted — users with an accepted direct connection
     */
    public function acceptedFriends(Request $request)
    {
        $user = $request->user();
        $friendIds = $user->conversations()
            ->where('type', 'direct')
            ->with('users:id')
            ->get()
            ->flatMap(fn ($conversation) => $conversation->users->pluck('id'))
            ->reject(fn ($id) => (int) $id === (int) $user->id)
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $friendIds)
            ->where(function ($query) {
                $query->where('profile_public', true)
                    ->orWhereNull('profile_public');
            })
            ->where(function ($query) {
                $query->where('allow_message_requests', true)
                    ->orWhereNull('allow_message_requests');
            })
            ->whereNotIn('id', $user->blocked_users ?? [])
            ->latest()
            ->get(['id', 'name', 'sohni_id', 'sohni_id_type', 'avatar_path']);

        return response()->json([
            'success' => true,
            'data' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'sohni_id' => $user->sohni_id,
                'sohni_id_type' => $user->sohni_id_type,
                'avatar_url' => $user->avatar_path ? '/storage/' . $user->avatar_path : null,
            ])->values(),
        ]);
    }

    /**
     * GET /api/followers/recent — get CURRENT LOGGED-IN user's recent followers only
     * IMPORTANT: Always returns data only for Auth::user(), never for other users
     */
    public function recentFollowers(Request $request)
    {
        $currentUser = $request->user(); // Only authenticated user
        
        // TODO: Query followers relationships for $currentUser when followers system is built
        // Query should: WHERE user_id = $currentUser->id, ORDER BY created_at DESC, LIMIT 20
        // Return: [{ id, name, avatar_url, sohni_id_type }, ...]
        
        return response()->json([
            'success' => true,
            'data' => [], // Empty until followers system implemented
        ]);
    }
}
