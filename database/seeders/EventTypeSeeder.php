<?php

namespace Database\Seeders;

use App\Models\EventType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param int|null $team_id
     * @return void
     */
    public function run($team_id = null): void
    {
        // If no team_id is provided, we'll create a default team or use team with ID 1.
        // This handles the `db:seed --class=EventTypeSeeder` use case.
        if (is_null($team_id)) {
            // Find or create a default user to own the team.
            $defaultUser = User::first() ?? User::factory()->create();
            // Find or create the default team.
            $defaultTeam = Team::firstOrCreate(
                ['id' => 1],
                [
                    'user_id' => $defaultUser->id,
                    'name' => "Equipo de ".$defaultUser->name,
                    'personal_team' => true,
                ]
            );
            $team_id = $defaultTeam->id;
        }

        // Add a guard to prevent creating duplicates for the same team.
        if (EventType::where('team_id', $team_id)->exists()) {
            return;
        }

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
                'is_all_day' => $eventType['is_all_day'],
            ]);
        }
    }
}
