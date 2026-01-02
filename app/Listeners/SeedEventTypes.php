<?php

namespace App\Listeners;

use App\Support\Permissions\PermissionMatrix;
use Laravel\Jetstream\Events\TeamCreated;

class SeedEventTypes
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Laravel\Jetstream\Events\TeamCreated  $event
     * @return void
     */
    public function handle(TeamCreated $event)
    {
        $team = $event->team;

        PermissionMatrix::syncTeamRoles($team->id, $team->user_id);

        if (!$team->workCenters()->exists()) {
            $team->workCenters()->create([
                'name' => 'Sede Central',
                'code' => 'HQ-' . str_pad((string) $team->id, 3, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!$team->eventTypes()->exists()) {
            $eventTypes = [
                [
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
                $team->eventTypes()->create(array_merge($eventType, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
