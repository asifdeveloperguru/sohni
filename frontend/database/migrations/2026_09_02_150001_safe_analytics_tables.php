<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_analytics')) {
            Schema::create('admin_analytics', function (Blueprint $table) {
                $table->id();
                $table->string('metric_type', 40);
                $table->integer('value')->default(0);
                $table->timestamp('recorded_at')->useCurrent();
                $table->index(['metric_type', 'recorded_at']);
            });
        }

        if (! Schema::hasTable('user_activity_logs')) {
            Schema::create('user_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('action', 40);
                $table->string('ip_address', 45)->nullable();
                $table->json('context')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'created_at']);
                $table->index(['action', 'created_at']);
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'login_count')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('login_count')->default(0)->after('last_seen_at');
                $table->unsignedBigInteger('data_usage_bytes')->default(0)->after('login_count');
                $table->timestamp('first_login_at')->nullable()->after('data_usage_bytes');
            });
        }

        if (Schema::hasTable('conversations') && ! Schema::hasColumn('conversations', 'message_count')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->unsignedInteger('message_count')->default(0)->after('is_locked');
                $table->unsignedBigInteger('total_media_size')->default(0)->after('message_count');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
        Schema::dropIfExists('admin_analytics');

        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropColumn(['message_count', 'total_media_size']);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['login_count', 'data_usage_bytes', 'first_login_at']);
            });
        }
    }
};
