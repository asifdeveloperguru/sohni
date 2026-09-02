<?php

use App\Models\Call;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Only invited participants of a live call may subscribe to its signaling channel.
Broadcast::channel('call.{roomId}', function ($user, string $roomId) {
    $call = Call::where('room_id', $roomId)->first();

    if (! $call || $call->status === 'ended' || ! $call->includes($user->id)) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar_path ? '/storage/' . $user->avatar_path : null,
    ];
});
