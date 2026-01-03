<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exceptional Clock In Tokens Table Migration
 * 
 * Temporary tokens for exceptional clock-ins.
 * Depends on: users, teams
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('exceptional_clock_in_tokens')) {
            Schema::create('exceptional_clock_in_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('token')->unique();
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exceptional_clock_in_tokens');
    }
};
