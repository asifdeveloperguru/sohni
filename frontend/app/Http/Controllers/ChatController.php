<?php

namespace App\Http\Controllers;

use App\Models\ChatUpload;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const MAX_UPLOAD_BYTES = 2147483648; // 2 GB

    private const CHUNK_SIZE = 4194304; // 4 MB of plaintext per chunk

    private const MAX_CIPHERTEXT_OVERHEAD = 33554432; // headroom for per-chunk nonce + MAC
    /**
     * GET /api/chat/conversations — list the user's conversations
     */
    public function conversations(Request $request)
    {
        $user = $request->user();
        $user->forceFill(['last_seen_at' => now()])->save();

        $conversations = $user->conversations()
            ->with(['lastMessage.user'])
            ->get()
            ->map(function (Conversation $conv) use ($user) {
                $unread = $conv->messages()
                    ->where('id', '>', $conv->pivot->last_read_message_id)
                    ->where('user_id', '!=', $user->id)
                    ->count();

                // For direct chats, show the other participant's name
                $name = $conv->name;
                $avatar = $conv->avatar;
                if ($conv->type === 'direct') {
                    $other = $conv->users->firstWhere('id', '!=', $user->id);
                    $name = $other?->name ?? 'Unknown';
                    $avatar = $other?->avatar_path ? '/storage/' . $other->avatar_path : null;
                }

                return [
                    'id' => $conv->id,
                    'name' => $name,
                    'type' => $conv->type,
                    'avatar' => $avatar,
                    'member_count' => $conv->users()->count(),
                    'last_message' => $conv->lastMessage?->body,
                    'last_message_by' => $conv->lastMessage?->user?->name,
                    'last_message_at' => $conv->lastMessage?->created_at?->toIso8601String(),
                    'unread' => $unread,
                ];
            })
            ->sortByDesc('last_message_at')
            ->values();

        return response()->json(['success' => true, 'data' => $conversations]);
    }

    /**
     * GET /api/chat/messages/{conversation}?after_id=N — realtime polling endpoint.
     * Pass after_id to get only new messages since the last poll.
     */
    public function messages(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($conversation->users()->where('user_id', $user->id)->exists(), 403);

        $afterId = (int) $request->query('after_id', 0);

        $query = $conversation->messages()->with('user:id,name,avatar_path,sohni_id');

        if ($afterId > 0) {
            $messages = $query->where('id', '>', $afterId)->orderBy('id')->get();
        } else {
            $messages = $query->orderByDesc('id')->limit(50)->get()->reverse()->values();
        }

        // Mark as read up to latest message
        if ($messages->isNotEmpty()) {
            $conversation->users()->updateExistingPivot($user->id, [
                'last_read_message_id' => $messages->last()->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $messages->map(fn (Message $m) => $this->formatMessage($m, $user->id)),
        ]);
    }

    /**
     * POST /api/chat/messages — send a message
     */
    public function send(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
            'body' => 'required|string|max:20000',
            'is_encrypted' => 'sometimes|boolean',
        ]);

        $conversation = Conversation::findOrFail($data['conversation_id']);
        abort_unless($conversation->users()->where('user_id', $user->id)->exists(), 403);

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $data['body'],
            'type' => 'text',
            'is_encrypted' => (bool) ($data['is_encrypted'] ?? false),
        ]);

        $conversation->touch();
        $conversation->users()->updateExistingPivot($user->id, [
            'last_read_message_id' => $message->id,
        ]);

        $message->load('user:id,name,avatar_path,sohni_id');

        return response()->json([
            'success' => true,
            'data' => $this->formatMessage($message, $user->id),
        ], 201);
    }

    /**
     * POST /api/chat/keys — publish this device's NaCl box public key.
     */
    public function publishKey(Request $request)
    {
        $data = $request->validate([
            'public_key' => 'required|string|max:100',
        ]);

        if (strlen(base64_decode($data['public_key'], true) ?: '') !== 32) {
            return response()->json(['success' => false, 'message' => 'Invalid public key.'], 422);
        }

        $request->user()->forceFill(['public_key' => $data['public_key']])->save();

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/chat/upload/init — reserve a chunked upload slot.
     */
    public function uploadInit(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
            'file_name' => 'required|string|max:255',
            'file_size' => 'required|integer|min:1|max:' . self::MAX_UPLOAD_BYTES,
        ]);

        $conversation = Conversation::findOrFail($data['conversation_id']);
        abort_unless($conversation->users()->where('user_id', $user->id)->exists(), 403);

        $uploadId = (string) Str::uuid();
        $path = 'chat-uploads/' . $uploadId . '.part';
        Storage::disk('local')->put($path, '');

        ChatUpload::create([
            'upload_id' => $uploadId,
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'path' => $path,
            'file_name' => $data['file_name'],
            'declared_size' => $data['file_size'],
        ]);

        return response()->json([
            'success' => true,
            'data' => ['upload_id' => $uploadId, 'chunk_size' => self::CHUNK_SIZE],
        ]);
    }

    /**
     * POST /api/chat/upload/chunk — append one encrypted chunk.
     */
    public function uploadChunk(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'upload_id' => 'required|uuid',
            'index' => 'required|integer|min:0',
            'chunk' => 'required|file',
        ]);

        $upload = ChatUpload::where('upload_id', $data['upload_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ((int) $data['index'] !== $upload->next_chunk) {
            return response()->json([
                'success' => false,
                'message' => 'Out-of-order chunk.',
                'expected' => $upload->next_chunk,
            ], 409);
        }

        $bytes = file_get_contents($data['chunk']->getRealPath());
        $received = $upload->received_size + strlen($bytes);

        // Ciphertext carries per-chunk overhead, so allow headroom over the plaintext size.
        if ($received > $upload->declared_size + self::MAX_CIPHERTEXT_OVERHEAD) {
            Storage::disk('local')->delete($upload->path);
            $upload->delete();

            return response()->json(['success' => false, 'message' => 'Upload exceeded declared size.'], 422);
        }

        $absolute = Storage::disk('local')->path($upload->path);
        file_put_contents($absolute, $bytes, FILE_APPEND | LOCK_EX);

        $upload->update([
            'received_size' => $received,
            'next_chunk' => $upload->next_chunk + 1,
        ]);

        return response()->json(['success' => true, 'data' => ['next_chunk' => $upload->next_chunk]]);
    }

    /**
     * POST /api/chat/upload/complete — turn a finished upload into a message.
     */
    public function uploadComplete(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'upload_id' => 'required|uuid',
            'type' => 'required|in:file,image,voice,video',
            'file_name' => 'required|string|max:255',
            'mime_type' => 'nullable|string|max:150',
            'duration' => 'nullable|integer|min:0',
            'media_keys' => 'required|array|min:1',
            'media_keys.*' => 'required|string|max:400',
        ]);

        $upload = ChatUpload::where('upload_id', $data['upload_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $conversation = Conversation::findOrFail($upload->conversation_id);
        abort_unless($conversation->users()->where('user_id', $user->id)->exists(), 403);

        $finalPath = 'chat-media/' . $upload->upload_id;
        Storage::disk('local')->move($upload->path, $finalPath);

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => '',
            'type' => $data['type'],
            'is_encrypted' => true,
            'file_path' => $finalPath,
            'file_name' => $data['file_name'],
            'file_size' => $upload->received_size,
            'mime_type' => $data['mime_type'] ?? 'application/octet-stream',
            'media_keys' => $data['media_keys'],
            'duration' => $data['duration'] ?? null,
        ]);

        $upload->delete();

        $conversation->touch();
        $conversation->users()->updateExistingPivot($user->id, [
            'last_read_message_id' => $message->id,
        ]);

        $message->load('user:id,name,avatar_path,sohni_id');

        return response()->json([
            'success' => true,
            'data' => $this->formatMessage($message, $user->id),
        ], 201);
    }

    /**
     * GET /api/chat/file/{message} — stream ciphertext to a participant.
     */
    public function downloadFile(Request $request, Message $message)
    {
        $user = $request->user();

        $conversation = $message->conversation;
        abort_unless($conversation && $conversation->users()->where('user_id', $user->id)->exists(), 403);
        abort_unless($message->file_path && Storage::disk('local')->exists($message->file_path), 404);

        return Storage::disk('local')->download($message->file_path, $message->file_name, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * POST /api/chat/start — start (or reuse) a direct chat by Sohni ID
     */
    public function start(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'identifier' => 'required|string|digits:14',
        ], [
            'identifier.digits' => 'Enter a valid 14-digit Sohni ID.',
        ]);

        $other = User::where('sohni_id', $data['identifier'])->first();

        if (! $other || $other->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No user found with that Sohni ID.',
            ], 404);
        }

        if ($other->allow_message_requests === false || $other->profile_public === false) {
            return response()->json([
                'success' => false,
                'message' => 'This user does not want to receive communication requests.',
            ], 403);
        }

        // Reuse an existing direct conversation between the two users
        $existing = Conversation::where('type', 'direct')
            ->whereHas('users', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('users', fn ($q) => $q->where('user_id', $other->id))
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'data' => ['conversation_id' => $existing->id]]);
        }

        $conversation = Conversation::create(['type' => 'direct', 'creator_id' => $user->id]);
        $conversation->users()->attach([$user->id, $other->id]);

        return response()->json([
            'success' => true,
            'data' => ['conversation_id' => $conversation->id],
        ], 201);
    }

    /**
     * POST /api/chat/request — send a chat request by scanning QR code (user_id)
     */
    public function sendRequest(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $other = User::findOrFail($data['user_id']);

        if ($other->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot start a chat with yourself.',
            ], 400);
        }

        if ($other->allow_message_requests === false || $other->profile_public === false) {
            return response()->json([
                'success' => false,
                'message' => 'This user does not want to receive communication requests.',
            ], 403);
        }

        // Reuse an existing direct conversation between the two users
        $existing = Conversation::where('type', 'direct')
            ->whereHas('users', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('users', fn ($q) => $q->where('user_id', $other->id))
            ->first();

        if ($existing) {
            return response()->json(['success' => true, 'data' => ['conversation_id' => $existing->id]]);
        }

        // Create new direct conversation
        $conversation = Conversation::create(['type' => 'direct', 'creator_id' => $user->id]);
        $conversation->users()->attach([$user->id, $other->id]);

        return response()->json([
            'success' => true,
            'message' => 'Chat request sent! Starting conversation...',
            'data' => ['conversation_id' => $conversation->id],
        ], 201);
    }

    /**
     * GET /api/chat/conversation/{id} — get a single conversation details for chat page
     */
    public function conversation(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        abort_unless($conversation->users()->where('user_id', $user->id)->exists(), 403);

        $other = $conversation->users->firstWhere('id', '!=', $user->id);
        
        if (!$other) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid conversation',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $conversation->id,
                'type' => $conversation->type,
                'me' => $user->id,
                'user_id' => $other->id,
                'name' => $other->name,
                'about' => $other->about_me,
                'avatar' => $other->avatar_path ? '/storage/' . $other->avatar_path : null,
                'sohni_id' => $other->sohni_id,
                'is_online' => $other->last_seen_at && $other->last_seen_at->isAfter(now()->subMinutes(5)),
                'encryption_key' => $other->public_key,
            ],
        ]);
    }

    private function formatMessage(Message $m, int $currentUserId): array
    {
        $isOwn = $m->user_id === $currentUserId;

        return [
            'id' => $m->id,
            'conversation_id' => $m->conversation_id,
            'body' => $m->body,
            'type' => $m->type,
            'message_type' => $m->type,
            'is_encrypted' => (bool) $m->is_encrypted,
            'own' => $isOwn,
            'is_own' => $isOwn,
            'file_url' => $m->file_path ? '/api/chat/file/' . $m->id : null,
            'file_name' => $m->file_name,
            'file_size' => $m->file_size,
            'mime_type' => $m->mime_type,
            'media_key' => $m->media_keys[(string) $currentUserId] ?? null,
            'duration' => $m->duration,
            'sender' => [
                'id' => $m->user->id,
                'name' => $m->user->name,
                'sohni_id' => $m->user->sohni_id,
                'avatar' => $m->user->avatar_path ? '/storage/' . $m->user->avatar_path : null,
            ],
            'time' => $m->created_at->format('g:i A'),
            'date' => $m->created_at->format('M j, Y'),
            'created_at' => $m->created_at->toIso8601String(),
        ];
    }
}
