<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Message User Pivot Table Migration
 * 
 * Recipients of messages.
 * Depends on: messages, users
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('message_user')) {
            Schema::create('message_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('read_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_user');
    }
};
