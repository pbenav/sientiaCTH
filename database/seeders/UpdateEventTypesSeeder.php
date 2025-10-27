<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EventType;
use Illuminate\Support\Facades\DB;

class UpdateEventTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Set is_all_day to false for existing, non-all-day types
        DB::table('event_types')
            ->whereIn('name', ['Jornada de trabajo', 'Pausa', 'Otros'])
            ->update(['is_all_day' => false]);

        // Example of adding a new all-day event type for teams that don't have one
        // This is commented out by default to avoid creating unwanted data,
        // but can be enabled if needed.
        /*
        $teams = DB::table('teams')->get();
        foreach ($teams as $team) {
            EventType::firstOrCreate(
                [
                    'team_id' => $team->id,
                    'name' => 'Vacaciones',
                ],
                [
                    'color' => '#0284c7', // sky-600
                    'is_all_day' => true,
                    'observations' => 'Evento de día completo para vacaciones.'
                ]
            );
        }
        */
    }
}
