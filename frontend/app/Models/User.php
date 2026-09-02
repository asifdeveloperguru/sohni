<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'sohni_id',
        'sohni_id_type',
        'address',
        'about_me',
        'experiences',
        'education',
        'avatar_path',
        'cover_path',
        'friends_count',
        'followers_count',
        'groups_count',
        'new_friends_this_week',
        'verification_token',
        'profile_completed_at',
        'last_seen_at',
        'accept_friend_requests',
        'show_online_status',
        'show_typing_indicators',
        'profile_public',
        'accept_qr_requests',
        'security_pin',
        'security_pattern',
        'active_devices',
        'last_activity_at',
        'last_login_at',
        'login_history',
        'require_pin_on_login',
        'require_pattern_on_login',
        'blocked_users',
        'privacy_whitelist',
        'allow_message_requests',
        'allow_group_invites',
        'allow_video_calls',
        'allow_screen_share',
        'session_timeout_hours',
        'trusted_devices',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * Personal fields use AES-256 'encrypted' casts — stored as ciphertext,
     * unreadable by anyone browsing the database.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'name' => 'encrypted',
            'first_name' => 'encrypted',
            'last_name' => 'encrypted',
            'phone' => 'encrypted',
            'address' => 'encrypted',
            'about_me' => 'encrypted',
            'accept_friend_requests' => 'boolean',
            'show_online_status' => 'boolean',
            'show_typing_indicators' => 'boolean',
            'profile_public' => 'boolean',
            'accept_qr_requests' => 'boolean',
            'require_pin_on_login' => 'boolean',
            'require_pattern_on_login' => 'boolean',
            'allow_message_requests' => 'boolean',
            'allow_group_invites' => 'boolean',
            'allow_video_calls' => 'boolean',
            'allow_screen_share' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'active_devices' => 'json',
            'login_history' => 'json',
            'blocked_users' => 'json',
            'privacy_whitelist' => 'json',
            'trusted_devices' => 'json',
            'experiences' => 'json',
        ];
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('last_read_message_id')
            ->withTimestamps();
    }

    public function reportsFiled()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
