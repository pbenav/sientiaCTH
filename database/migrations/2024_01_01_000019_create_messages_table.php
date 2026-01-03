<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messages Table Migration
 * 
 * Internal messaging system.
 * Depends on: users
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users');
                $table->foreignId('parent_id')->nullable()->constrained('messages')->onDelete('cascade');
                $table->string('subject');
                $table->text('body');
                $table->timestamp('sender_deleted_at')->nullable();
                $table->timestamp('sender_purged_at')->nullable();
                $table->timestamps();

                $table->index('parent_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
