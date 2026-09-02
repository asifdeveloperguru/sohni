<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = ['user_id', 'title', 'completion_date', 'grade', 'marks'];

    /**
     * AES-256 encrypted at rest — unreadable in the database.
     */
    protected function casts(): array
    {
        return [
            'title' => 'encrypted',
            'completion_date' => 'encrypted',
            'grade' => 'encrypted',
            'marks' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
