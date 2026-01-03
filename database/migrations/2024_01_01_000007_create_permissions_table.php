<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permissions Table Migration
 * 
 * Creates the permissions table for the permission system.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name');
                $table->text('description')->nullable();
                $table->string('category', 100)->nullable();
                $table->boolean('requires_context')->default(false);
                $table->boolean('is_system')->default(false);
                $table->timestamps();

                $table->index('category');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
