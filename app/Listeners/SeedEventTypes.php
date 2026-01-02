<?php

namespace App\Listeners;

use Laravel\Jetstream\Events\TeamCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Database\Seeders\EventTypeSeeder;
use Illuminate\Support\Facades\App;

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
        if (!App::environment('testing')) {
            $team = $event->team;

            // 1. Create default roles for the new team
            $roles = [
                ['name' => 'admin', 'display_name' => 'Administrador', 'description' => 'Control total del equipo y configuración.'],
                ['name' => 'inspector', 'display_name' => 'Inspector', 'description' => 'Puede ver todos los registros e informes pero no modificar configuración.'],
                ['name' => 'user', 'display_name' => 'Usuario', 'description' => 'Acceso estándar para fichaje y consulta de datos propios.'],
            ];

            foreach ($roles as $roleData) {
                $role = \App\Models\Role::firstOrCreate(
                    ['team_id' => $team->id, 'name' => $roleData['name']],
                    [
                        'display_name' => $roleData['display_name'],
                        'description' => $roleData['description'],
                        'is_system' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Assign teams.create permission to the admin role
                if ($roleData['name'] === 'admin') {
                    $permission = \App\Models\Permission::where('name', 'teams.create')->first();
                    if ($permission) {
                        $role->permissions()->syncWithoutDetaching([$permission->id]);
                    }
                }
            }

            // 2. Create default work center
            if ($team->workCenters()->count() === 0) {
                $team->workCenters()->create([
                    'name' => 'Sede Central',
                    'code' => 'HQ-' . str_pad((string)$team->id, 3, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Create default event types
            if ($team->eventTypes()->count() === 0) {
                $eventTypes = [
                    [
                        'name' => 'Jornada Laboral',
                        'observations' => 'Evento principal de trabajo.',
                        'color' => '#10b981', // Green
                        'is_all_day' => false,
                        'is_workday_type' => true,
                        'is_break_type' => false,
                        'is_pause_type' => false,
                        'is_authorizable' => false,
                    ],
                    [
                        'name' => 'Vacaciones',
                        'observations' => 'Días de descanso anual.',
                        'color' => '#3b82f6', // Blue
                        'is_all_day' => true,
                        'is_workday_type' => false,
                        'is_break_type' => false,
                        'is_pause_type' => false,
                        'is_authorizable' => true,
                    ],
                    [
                        'name' => 'Asuntos Propios',
                        'observations' => 'Días de libre disposición.',
                        'color' => '#8b5cf6', // Purple
                        'is_all_day' => true,
                        'is_workday_type' => false,
                        'is_break_type' => false,
                        'is_pause_type' => false,
                        'is_authorizable' => true,
                    ],
                    [
                        'name' => 'Pausa',
                        'observations' => 'Interrupción temporal de la jornada.',
                        'color' => '#f59e0b', // Orange
                        'is_all_day' => false,
                        'is_workday_type' => false,
                        'is_break_type' => false,
                        'is_pause_type' => true,
                        'is_authorizable' => false,
                    ],
                    [
                        'name' => 'Evento Especial',
                        'observations' => 'Eventos fuera de lo común.',
                        'color' => '#ef4444', // Red
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
}
