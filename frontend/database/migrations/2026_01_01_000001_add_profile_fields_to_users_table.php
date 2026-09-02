<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('sohni_id', 50)->nullable()->unique();
            $table->string('sohni_id_type', 20)->default('free'); // free | premium
            $table->string('address')->nullable();
            $table->string('education')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('verification_token', 64)->nullable();
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'last_name', 'phone', 'sohni_id', 'sohni_id_type',
                'address', 'education', 'avatar_path', 'verification_token',
                'profile_completed_at', 'last_seen_at',
            ]);
        });
    }
};
