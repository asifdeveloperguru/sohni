<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Encrypted-at-rest degree records — multiple per user
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('title');            // encrypted
            $table->text('completion_date');  // encrypted
            $table->text('grade')->nullable();   // encrypted
            $table->text('marks')->nullable();   // encrypted
            $table->timestamps();
        });

        // Widen personal columns to hold encrypted payloads
        Schema::table('users', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('first_name')->nullable()->change();
            $table->text('last_name')->nullable()->change();
            $table->text('phone')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('education')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
