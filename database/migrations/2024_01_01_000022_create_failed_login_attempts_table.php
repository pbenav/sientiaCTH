<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Failed Login Attempts Table Migration
 * 
 * Security tracking for failed login attempts.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('failed_login_attempts')) {
            Schema::create('failed_login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45);
                $table->timestamp('timestamp');
                $table->integer('lockout_time')->nullable();
                $table->timestamps();

                $table->index('ip_address');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_login_attempts');
    }
};
