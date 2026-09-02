<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Session Management
            $table->timestamp('last_activity_at')->nullable()->after('deleted_at');
            $table->timestamp('last_login_at')->nullable()->after('last_activity_at');
            $table->json('login_history')->nullable()->after('last_login_at');
            
            // Enhanced Privacy Settings
            $table->boolean('require_pin_on_login')->default(false)->after('login_history');
            $table->boolean('require_pattern_on_login')->default(false)->after('require_pin_on_login');
            $table->json('blocked_users')->nullable()->after('require_pattern_on_login');
            $table->json('privacy_whitelist')->nullable()->after('blocked_users');
            $table->boolean('allow_message_requests')->default(true)->after('privacy_whitelist');
            $table->boolean('allow_group_invites')->default(true)->after('allow_message_requests');
            $table->boolean('allow_video_calls')->default(true)->after('allow_group_invites');
            $table->boolean('allow_screen_share')->default(true)->after('allow_video_calls');
            $table->integer('session_timeout_hours')->default(72)->after('allow_screen_share');
            $table->json('trusted_devices')->nullable()->after('session_timeout_hours');
            $table->boolean('two_factor_enabled')->default(false)->after('trusted_devices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
