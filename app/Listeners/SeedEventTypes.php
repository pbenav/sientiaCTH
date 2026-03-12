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

        // Create welcome announcement for the new team
        if (!$team->announcements()->exists()) {
            $team->announcements()->create([
                'title' => '¡Bienvenido a sientiaCTH!',
                'content' => '<h2>¡Hola! Bienvenido a sientiaCTH (Control de Tiempo y Horarios)</h2>
                             <p>Esta aplicación ha sido diseñada para facilitar la gestión del tiempo laboral, permitiendo un registro preciso y transparente de la jornada de trabajo.</p>
                             <p><strong>¿Qué es sientiaCTH?</strong> Es una solución integral que combina una plataforma web avanzada con una aplicación móvil intuitiva, permitiendo fichajes mediante SmartClockIn, gestión de pausas, vacaciones y mucho más.</p>
                             <p><strong>¿Quién ha hecho esto?</strong> Este sistema ha sido desarrollado íntegramente por <strong>Sientia</strong>, con el objetivo de modernizar y simplificar el control horario empresarial.</p>
                             <p>Esperamos que esta herramienta te sea de gran utilidad. ¡Empecemos a trabajar!</p>',
                'format' => 'html',
                'is_active' => true,
                'created_by' => $team->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
