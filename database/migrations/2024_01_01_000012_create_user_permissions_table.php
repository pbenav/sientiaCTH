<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User Permissions Table Migration
 * 
 * Direct permission assignments to users.
 * Depends on: users, permissions, teams
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
                $table->json('context')->nullable();
                $table->timestamp('valid_from')->nullable();
                $table->timestamp('valid_until')->nullable();
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('granted_at')->useCurrent();
                $table->foreignId('revoked_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('revoked_at')->nullable();

                $table->index(['user_id', 'valid_until']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
