<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permission Audit Log Table Migration
 * 
 * Audit trail for permission checks and assignments.
 * Depends on: users, teams
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permission_audit_log')) {
            Schema::create('permission_audit_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('permission_name');
                $table->string('action', 50);
                $table->enum('result', ['allowed', 'denied'])->nullable();
                $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('resource_type', 100)->nullable();
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->json('context')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['user_id', 'created_at']);
                $table->index('permission_name');
                $table->index('action');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_audit_log');
    }
};
