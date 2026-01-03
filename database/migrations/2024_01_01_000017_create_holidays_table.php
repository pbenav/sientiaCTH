<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Holidays Table Migration
 * 
 * National/regional holidays by team.
 * Depends on: teams
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('date');
                $table->string('type')->nullable();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['date', 'team_id']);
                $table->index('date');
                $table->index('type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
