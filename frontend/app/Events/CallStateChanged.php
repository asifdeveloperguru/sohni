<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomId,
        public string $action,
        public array $data = [],
    ) {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('call.' . $this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'state';
    }

    public function broadcastWith(): array
    {
        return ['action' => $this->action, 'data' => $this->data];
    }
}
