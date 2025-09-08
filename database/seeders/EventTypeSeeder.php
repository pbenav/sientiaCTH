<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($team_id): void
    {
        $eventTypes = [
            [
                'name' => 'Jornada laboral',
                'color' => '#ff0000ff',
                'observations' => 'Horas trabajadas en un día normal.',
                'is_all_day' => false,
            ],
            [
                'name' => 'Asuntos propios',
                'color' => '#ffa600ff',
                'observations' => 'Días de permiso para asuntos personales.',
                'is_all_day' => true,
            ],
            [
                'name' => 'Vacaciones',
                'color' => '#22c55e',
                'observations' => 'Días de vacaciones anuales.',
                'is_all_day' => true,
            ],
            [
                'name' => 'Evento especial',
                'color' => '#8b5cf6',
                'observations' => 'Eventos especiales como reuniones, desplazamientos, etc.',
                'is_all_day' => false,
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
