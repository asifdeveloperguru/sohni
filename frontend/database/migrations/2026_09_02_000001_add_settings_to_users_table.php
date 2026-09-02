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
            // Privacy & Notifications Settings
            $table->boolean('accept_friend_requests')->default(true)->after('cover_path');
            $table->boolean('show_online_status')->default(true)->after('accept_friend_requests');
            $table->boolean('show_typing_indicators')->default(true)->after('show_online_status');
            $table->boolean('profile_public')->default(true)->after('show_typing_indicators');
            $table->boolean('accept_qr_requests')->default(true)->after('profile_public');
            
            // Security Settings
            $table->string('security_pin')->nullable()->after('accept_qr_requests');
            $table->string('security_pattern')->nullable()->after('security_pin');
            $table->json('active_devices')->nullable()->after('security_pattern');
            
            // Soft delete for account deletion
            $table->softDeletes()->after('active_devices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'accept_friend_requests',
                'show_online_status',
                'show_typing_indicators',
                'profile_public',
                'accept_qr_requests',
                'security_pin',
                'security_pattern',
                'active_devices',
                'deleted_at',
            ]);
        });
    }
};
