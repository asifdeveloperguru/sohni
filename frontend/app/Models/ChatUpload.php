<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatUpload extends Model
{
    protected $fillable = [
        'upload_id', 'conversation_id', 'user_id', 'path',
        'file_name', 'declared_size', 'received_size', 'next_chunk',
    ];
}
