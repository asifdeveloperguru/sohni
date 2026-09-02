<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // NaCl box public key (base64). The secret key never leaves the device.
            $table->text('public_key')->nullable()->after('remember_token');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_encrypted')->default(false)->after('type');
            $table->string('file_path')->nullable()->after('is_encrypted');
            $table->string('file_name')->nullable()->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_name');
            $table->string('mime_type')->nullable()->after('file_size');
            // Per-file symmetric key, sealed separately for each participant.
            $table->text('media_keys')->nullable()->after('mime_type');
            $table->unsignedInteger('duration')->nullable()->after('media_keys');
        });

        Schema::create('chat_uploads', function (Blueprint $table) {
            $table->id();
            $table->uuid('upload_id')->unique();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('file_name');
            $table->unsignedBigInteger('declared_size');
            $table->unsignedBigInteger('received_size')->default(0);
            $table->unsignedInteger('next_chunk')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_uploads');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['is_encrypted', 'file_path', 'file_name', 'file_size', 'mime_type', 'media_keys', 'duration']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('public_key');
        });
    }
};
