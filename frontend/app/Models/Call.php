<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    protected $fillable = [
        'room_id', 'conversation_id', 'host_id', 'mode',
        'status', 'max_participants', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(CallParticipant::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function activeParticipants(): HasMany
    {
        return $this->participants()->where('state', 'joined');
    }

    public function includes(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }
}
