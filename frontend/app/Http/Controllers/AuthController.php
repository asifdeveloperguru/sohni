<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * POST /api/auth/signup
     */
    public function signup(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => 'required|string|min:8|confirmed|regex:/[A-Z]/|regex:/[a-z]/|regex:/\d/',
        ], [
            'email.unique' => 'This email is already registered. Please sign in.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $email = strtolower($data['email']);

        $user = User::create([
            'name' => explode('@', $email)[0], // placeholder until profile setup
            'email' => $email,
            'password' => $data['password'],
            'verification_token' => Str::random(48),
        ]);

        try {
            $this->sendVerificationMail($user);
        } catch (\Exception $e) {
            \Log::error('Email send failed during signup: ' . $e->getMessage());
            // Don't fail signup even if email fails
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Account created. A verification link has been sent to your email.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'verification_status' => 'pending',
                'redirect' => '/verify-email',
            ],
        ], 201);
    }

    /**
     * POST /api/auth/signin
     */
    public function signin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required|string',
        ]);

        $user = User::where('email', strtolower($data['email']))->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $history = $user->login_history ?? [];
        array_unshift($history, [
            'timestamp' => now()->toIso8601String(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $user->forceFill([
            'last_login_at' => now(),
            'last_activity_at' => now(),
            'login_history' => array_slice($history, 0, 50),
        ])->save();

        // Decide where the user should go next
        $redirect = '/dashboard';
        if (! $user->email_verified_at) {
            $redirect = '/verify-email';
        } elseif (! $user->profile_completed_at) {
            $redirect = '/profile-setup';
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'redirect' => $redirect,
            ],
        ]);
    }

    /**
     * GET /activate/{id}/{token} — signed link from the email opens the activation page
     */
    public function showActivation(Request $request, int $id, string $token)
    {
        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            return redirect('/account');
        }

        if (! $user->verification_token || ! hash_equals($user->verification_token, $token)) {
            abort(403, 'Invalid or already-used activation link.');
        }

        // POST back to this same signed URL so the signature stays valid
        return view('activate', [
            'user' => $user,
            'actionUrl' => $request->fullUrl(),
        ]);
    }

    /**
     * POST /activate/{id}/{token} — the "Verify & Activate" button
     */
    public function activate(Request $request, int $id, string $token)
    {
        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            return redirect('/account');
        }

        if (! $user->verification_token || ! hash_equals($user->verification_token, $token)) {
            abort(403, 'Invalid or already-used activation link.');
        }

        // Single-use: token is destroyed on success
        $user->forceFill([
            'email_verified_at' => now(),
            'verification_token' => null,
        ])->save();

        // Auto-join the community group so new users have someone to talk to
        $community = Conversation::firstOrCreate(
            ['name' => 'Sohni Community 🇵🇰', 'type' => 'group'],
            ['creator_id' => $user->id]
        );
        $community->users()->syncWithoutDetaching([$user->id]);

        // Fresh, session-fixation-safe login bound to this browser only
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/profile-setup');
    }

    /**
     * GET /api/auth/verification-status — polled by the verify-email page
     */
    public function verificationStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $user->email,
                'verified' => (bool) $user->email_verified_at,
                'profile_complete' => (bool) $user->profile_completed_at,
            ],
        ]);
    }

    /**
     * POST /api/auth/resend-verification
     */
    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.',
            ], 400);
        }

        // New single-use token per resend — old links stop working
        $user->forceFill(['verification_token' => Str::random(48)])->save();

        try {
            $this->sendVerificationMail($user);
        } catch (\Exception $e) {
            \Log::error('Email send failed during resend: ' . $e->getMessage());
            // Don't fail resend even if email fails - user can try again
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification email resent to ' . $user->email,
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    private function sendVerificationMail(User $user): void
    {
        // Tamper-proof, expiring link (60 min) — signature covers id + token
        $link = URL::temporarySignedRoute('activate.show', now()->addMinutes(60), [
            'id' => $user->id,
            'token' => $user->verification_token,
        ]);

        Mail::send('emails.verify-account', [
            'user' => $user,
            'link' => $link,
        ], function ($message) use ($user) {
            $message->to($user->email)->subject('✨ Activate your Sohni account');
        });
    }
}
