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
            $table->unsignedInteger('friends_count')->default(0)->after('cover_path');
            $table->unsignedInteger('followers_count')->default(0)->after('friends_count');
            $table->unsignedInteger('groups_count')->default(0)->after('followers_count');
            $table->unsignedInteger('new_friends_this_week')->default(0)->after('groups_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['friends_count', 'followers_count', 'groups_count', 'new_friends_this_week']);
        });
    }
};
