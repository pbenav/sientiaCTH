<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permission Role Pivot Table Migration
 * 
 * Links permissions to roles.
 * Depends on: permissions, roles, users
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained()->onDelete('cascade');
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('granted_at')->useCurrent();

                $table->unique(['permission_id', 'role_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
