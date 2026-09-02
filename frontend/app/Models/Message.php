<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id', 'user_id', 'body', 'type', 'is_encrypted',
        'file_path', 'file_name', 'file_size', 'mime_type', 'media_keys', 'duration',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'media_keys' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
