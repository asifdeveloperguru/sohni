<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_sessions', function (Blueprint $table) {
            $table->boolean('revoked')->default(false)->after('last_activity');
        });
    }

    public function down(): void
    {
        Schema::table('admin_sessions', function (Blueprint $table) {
            $table->dropColumn('revoked');
        });
    }
};
