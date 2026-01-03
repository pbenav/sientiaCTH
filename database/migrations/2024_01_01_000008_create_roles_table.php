<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roles Table Migration
 * 
 * Creates the roles table for the permission system.
 * Depends on: teams, users
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('display_name');
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->unique(['team_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
