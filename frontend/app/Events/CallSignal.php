<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Relays one SDP/ICE payload to a single peer. The server never inspects it.
 */
class CallSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomId,
        public int $fromUserId,
        public int $toUserId,
        public string $kind,
        public array $payload,
    ) {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('call.' . $this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'signal';
    }

    public function broadcastWith(): array
    {
        return [
            'from' => $this->fromUserId,
            'to' => $this->toUserId,
            'kind' => $this->kind,
            'payload' => $this->payload,
        ];
    }
}
