<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Events History Table Migration
 * 
 * Audit trail for event modifications.
 * Depends on: users
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events_history')) {
            Schema::create('events_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('tablename');
                $table->string('original_event', 2048);
                $table->string('modified_event', 2048);
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events_history');
    }
};
