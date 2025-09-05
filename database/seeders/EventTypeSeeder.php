<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($team_id = 1): void
    {
        $eventTypes = [
            [
                'name' => 'Jornada laboral',
                'color' => '#0284c7',
                'observations' => 'Horas trabajadas en un día normal.'
            ],
            [
                'name' => 'Asuntos propios',
                'color' => '#eab308',
                'observations' => 'Días de permiso para asuntos personales.'
            ],
            [
                'name' => 'Vacaciones',
                'color' => '#22c55e',
                'observations' => 'Días de vacaciones anuales.'
            ],
            [
                'name' => 'Evento especial',
                'color' => '#8b5cf6',
                'observations' => 'Eventos especiales como reuniones, desplazamientos, etc.'
            ],
        ];

        foreach ($eventTypes as $eventType) {
            EventType::create([
                'team_id' => $team_id,
                'name' => $eventType['name'],
                'color' => $eventType['color'],
                'observations' => $eventType['observations'],
            ]);
        }
    }
}
