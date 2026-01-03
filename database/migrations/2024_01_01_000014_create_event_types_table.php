<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Event Types Table Migration
 * 
 * Defines types of events (work, vacation, breaks, etc.).
 * Depends on: teams
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_types')) {
            Schema::create('event_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->text('observations')->nullable();
                $table->string('color')->nullable();
                $table->boolean('is_all_day')->default(false);
                $table->boolean('is_workday_type')->default(false);
                $table->boolean('is_break_type')->default(false);
                $table->boolean('is_authorizable')->default(false);
                $table->boolean('is_pause_type')->default(false);
                $table->timestamps();

                $table->index('is_workday_type');
            });
        } else {
            $this->addMissingColumns();
        }

        // Ensure basic event types exist
        $this->ensureBasicEventTypes();
    }

    private function addMissingColumns(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            if (!Schema::hasColumn('event_types', 'is_pause_type')) {
                $table->boolean('is_pause_type')->default(false)->after('is_authorizable');
            }
        });
    }

    private function ensureBasicEventTypes(): void
    {
        $eventTypes = [
            [
                'id' => 1,
                'team_id' => 1,
                'name' => 'Jornada Laboral',
                'observations' => 'Evento principal de trabajo.',
                'color' => '#10b981',
                'is_all_day' => false,
                'is_workday_type' => true,
                'is_break_type' => false,
                'is_pause_type' => false,
                'is_authorizable' => false,
            ],
            [
                'id' => 2,
                'team_id' => 1,
                'name' => 'Vacaciones',
                'observations' => 'Días de descanso anual.',
                'color' => '#3b82f6',
                'is_all_day' => true,
                'is_workday_type' => false,
                'is_break_type' => false,
                'is_pause_type' => false,
                'is_authorizable' => true,
            ],
            [
                'id' => 3,
                'team_id' => 1,
                'name' => 'Asuntos Propios',
                'observations' => 'Días de libre disposición.',
                'color' => '#8b5cf6',
                'is_all_day' => true,
                'is_workday_type' => false,
                'is_break_type' => false,
                'is_pause_type' => false,
                'is_authorizable' => true,
            ],
            [
                'id' => 4,
                'team_id' => 1,
                'name' => 'Pausa',
                'observations' => 'Interrupción temporal de la jornada.',
                'color' => '#f59e0b',
                'is_all_day' => false,
                'is_workday_type' => false,
                'is_break_type' => false,
                'is_pause_type' => true,
                'is_authorizable' => false,
            ],
            [
                'id' => 5,
                'team_id' => 1,
                'name' => 'Evento Especial',
                'observations' => 'Eventos fuera de lo común.',
                'color' => '#ef4444',
                'is_all_day' => false,
                'is_workday_type' => false,
                'is_break_type' => false,
                'is_pause_type' => false,
                'is_authorizable' => false,
            ],
        ];

        foreach ($eventTypes as $eventType) {
            DB::table('event_types')->updateOrInsert(['id' => $eventType['id']], array_merge($eventType, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_types');
    }
};
