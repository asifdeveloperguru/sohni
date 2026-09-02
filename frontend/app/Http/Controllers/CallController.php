<?php

namespace App\Http\Controllers;

use App\Events\CallSignal;
use App\Events\CallStateChanged;
use App\Events\IncomingCall;
use App\Models\Call;
use App\Models\CallParticipant;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CallController extends Controller
{
    /**
     * Mesh WebRTC means every participant uploads one stream per peer, so the
     * ceiling is deliberately low. Raising it requires an SFU, not a config change.
     */
    private const MAX_PARTICIPANTS = 6;

    /**
     * GET /api/calls/ice — ICE servers for the client's RTCPeerConnection.
     */
    public function iceServers()
    {
        $servers = [
            ['urls' => ['stun:stun.l.google.com:19302', 'stun:stun1.l.google.com:19302']],
        ];

        $turnUrls = array_filter(array_map('trim', explode(',', (string) config('services.turn.urls'))));

        if ($turnUrls) {
            $servers[] = [
                'urls' => array_values($turnUrls),
                'username' => config('services.turn.username'),
                'credential' => config('services.turn.credential'),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ice_servers' => $servers,
                'has_turn' => (bool) $turnUrls,
                'max_participants' => self::MAX_PARTICIPANTS,
            ],
        ]);
    }

    /**
     * POST /api/calls — open a room and ring the invitees.
     */
    public function start(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'conversation_id' => 'nullable|integer|exists:conversations,id',
            'mode' => 'required|in:audio,video',
            'user_ids' => 'required|array|min:1|max:' . (self::MAX_PARTICIPANTS - 1),
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $invitees = collect($data['user_ids'])->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $user->id)
            ->unique()
            ->values();

        if ($invitees->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Invite at least one other person.'], 422);
        }

        if ($data['conversation_id']) {
            $conversation = Conversation::findOrFail($data['conversation_id']);
            abort_unless($conversation->users()->where('user_id', $user->id)->exists(), 403);

            // Only ring people who are actually in the conversation.
            $memberIds = $conversation->users()->pluck('users.id');
            $invitees = $invitees->intersect($memberIds)->values();

            if ($invitees->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Those users are not in this conversation.'], 403);
            }
        }

        $call = Call::create([
            'room_id' => (string) Str::uuid(),
            'conversation_id' => $data['conversation_id'] ?? null,
            'host_id' => $user->id,
            'mode' => $data['mode'],
            'status' => 'ringing',
            'max_participants' => self::MAX_PARTICIPANTS,
        ]);

        $call->participants()->create([
            'user_id' => $user->id,
            'state' => 'joined',
            'joined_at' => now(),
        ]);

        foreach ($invitees as $inviteeId) {
            $call->participants()->create(['user_id' => $inviteeId, 'state' => 'invited']);

            broadcast(new IncomingCall($inviteeId, [
                'room_id' => $call->room_id,
                'mode' => $call->mode,
                'from' => ['id' => $user->id, 'name' => $user->name],
            ]));
        }

        return response()->json(['success' => true, 'data' => $this->roomPayload($call, $user->id)], 201);
    }

    /**
     * POST /api/calls/{call:room_id}/join
     */
    public function join(Request $request, Call $call)
    {
        $user = $request->user();

        if ($call->status === 'ended') {
            return response()->json(['success' => false, 'message' => 'This call has ended.'], 410);
        }

        $participant = $call->participants()->where('user_id', $user->id)->first();

        if (! $participant) {
            return response()->json(['success' => false, 'message' => 'You were not invited to this call.'], 403);
        }

        if ($call->activeParticipants()->where('user_id', '!=', $user->id)->count() >= $call->max_participants - 1) {
            return response()->json(['success' => false, 'message' => 'This call is full.'], 409);
        }

        $participant->update(['state' => 'joined', 'joined_at' => now(), 'left_at' => null]);

        if ($call->status === 'ringing') {
            $call->update(['status' => 'active', 'started_at' => now()]);
        }

        broadcast(new CallStateChanged($call->room_id, 'joined', [
            'user' => ['id' => $user->id, 'name' => $user->name],
        ]))->toOthers();

        return response()->json(['success' => true, 'data' => $this->roomPayload($call->fresh(), $user->id)]);
    }

    /**
     * POST /api/calls/{call:room_id}/decline
     */
    public function decline(Request $request, Call $call)
    {
        $user = $request->user();

        $participant = $call->participants()->where('user_id', $user->id)->firstOrFail();
        $participant->update(['state' => 'declined']);

        broadcast(new CallStateChanged($call->room_id, 'declined', [
            'user' => ['id' => $user->id, 'name' => $user->name],
        ]));

        $this->endIfEmpty($call);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/calls/{call:room_id}/leave
     */
    public function leave(Request $request, Call $call)
    {
        $user = $request->user();

        $participant = $call->participants()->where('user_id', $user->id)->first();

        if ($participant) {
            $participant->update(['state' => 'left', 'left_at' => now()]);
        }

        broadcast(new CallStateChanged($call->room_id, 'left', [
            'user' => ['id' => $user->id, 'name' => $user->name],
        ]));

        $this->endIfEmpty($call);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/calls/{call:room_id}/signal — relay one SDP/ICE payload.
     */
    public function signal(Request $request, Call $call)
    {
        $user = $request->user();

        $data = $request->validate([
            'to' => 'required|integer',
            'kind' => 'required|in:offer,answer,candidate,renegotiate',
            'payload' => 'required|array',
        ]);

        abort_unless($call->includes($user->id), 403);

        // Refuse to relay to anyone who is not part of this room.
        if (! $call->includes((int) $data['to'])) {
            return response()->json(['success' => false, 'message' => 'Unknown peer.'], 403);
        }

        broadcast(new CallSignal($call->room_id, $user->id, (int) $data['to'], $data['kind'], $data['payload']));

        return response()->json(['success' => true]);
    }

    /**
     * GET /api/calls/{call:room_id}
     */
    public function show(Request $request, Call $call)
    {
        $user = $request->user();
        abort_unless($call->includes($user->id), 403);

        return response()->json(['success' => true, 'data' => $this->roomPayload($call, $user->id)]);
    }

    private function endIfEmpty(Call $call): void
    {
        if ($call->activeParticipants()->count() > 0) {
            return;
        }

        $call->update(['status' => 'ended', 'ended_at' => now()]);
        broadcast(new CallStateChanged($call->room_id, 'ended'));
    }

    private function roomPayload(Call $call, int $currentUserId): array
    {
        $call->load('participants.user:id,name,avatar_path');

        return [
            'room_id' => $call->room_id,
            'conversation_id' => $call->conversation_id,
            'mode' => $call->mode,
            'status' => $call->status,
            'host_id' => $call->host_id,
            'me' => $currentUserId,
            'max_participants' => $call->max_participants,
            'participants' => $call->participants->map(fn (CallParticipant $p) => [
                'id' => $p->user_id,
                'name' => $p->user->name,
                'avatar' => $p->user->avatar_path ? '/storage/' . $p->user->avatar_path : null,
                'state' => $p->state,
            ])->values(),
        ];
    }
}
