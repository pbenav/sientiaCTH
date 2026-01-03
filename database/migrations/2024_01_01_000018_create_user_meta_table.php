<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User Meta Table Migration
 * 
 * Key-value storage for user metadata.
 * Depends on: users
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_meta')) {
            Schema::create('user_meta', function (Blueprint $table) {
                $table->id();
                $table->string('meta_key');
                $table->text('meta_value')->nullable();
                $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'meta_key']);
                $table->index('user_id');
                $table->index('meta_key');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_meta');
    }
};
