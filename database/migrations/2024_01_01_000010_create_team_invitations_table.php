<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Team Invitations Table Migration
 * 
 * Manages team invitations (Jetstream).
 * Depends on: teams
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('team_invitations')) {
            Schema::create('team_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('email');
                $table->string('role')->nullable();
                $table->timestamps();

                $table->unique(['team_id', 'email']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_invitations');
    }
};
