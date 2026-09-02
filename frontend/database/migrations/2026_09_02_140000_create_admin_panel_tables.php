<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role', 20)->default('moderator'); // super_admin | admin | moderator
            $table->string('totp_secret')->nullable();
            $table->boolean('totp_enabled')->default(false);
            $table->json('recovery_codes')->nullable();
            $table->string('status', 12)->default('active'); // active | suspended
            $table->unsignedInteger('failed_logins')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('admin_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity');
            $table->timestamps();
        });

        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('target_type', 40)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->string('reason', 40)->default('other');
            $table->text('details')->nullable();
            $table->string('status', 12)->default('open'); // open | reviewing | resolved | dismissed
            $table->foreignId('resolved_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('last_seen_at');
            $table->string('ban_reason')->nullable()->after('is_banned');
            $table->timestamp('banned_at')->nullable()->after('ban_reason');
            $table->foreignId('banned_by')->nullable()->constrained('admin_users')->nullOnDelete()->after('banned_at');
            $table->boolean('is_suspended')->default(false)->after('banned_by');
            $table->timestamp('suspended_until')->nullable()->after('is_suspended');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('creator_id');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('banned_by');
            $table->dropColumn(['is_banned', 'ban_reason', 'banned_at', 'is_suspended', 'suspended_until']);
        });

        Schema::dropIfExists('reports');
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('admin_sessions');
        Schema::dropIfExists('admin_users');
    }
};
