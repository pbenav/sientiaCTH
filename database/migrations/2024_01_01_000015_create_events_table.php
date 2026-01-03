<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Events Table Migration
 * 
 * Main time tracking table storing all clock-in/clock-out events.
 * Depends on: users, teams, event_types, work_centers
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
                $table->dateTime('start');
                $table->dateTime('end')->nullable();
                $table->string('description')->nullable();
                $table->foreignId('event_type_id')->nullable()->constrained()->onDelete('set null');
                $table->text('observations')->nullable();
                $table->foreignId('work_center_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('authorized_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->boolean('is_open');
                $table->boolean('is_authorized')->default(false);
                $table->boolean('is_closed_automatically')->default(false);
                $table->boolean('is_exceptional')->default(false);
                $table->boolean('is_extra_hours')->default(false);
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('nfc_tag_id', 100)->nullable();
                $table->timestamps();

                // Strategic indexes for performance
                $table->index(['user_id', 'start', 'end'], 'events_user_date_range_idx');
                $table->index(['team_id', 'start', 'end'], 'events_team_date_range_idx');
                $table->index(['event_type_id', 'start'], 'events_type_date_idx');
                $table->index(['user_id', 'is_open'], 'events_user_open_idx');
                $table->index('work_center_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
