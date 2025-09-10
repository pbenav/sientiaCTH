<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\EventType;
use App\Models\Team;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get all teams
        $teams = Team::all();

        foreach ($teams as $team) {
            // Find the event types for the current team
            $workdayType = EventType::where('team_id', $team->id)
                                    ->where('name', 'Jornada laboral')->first();
            $specialEventType = EventType::where('team_id', $team->id)
                                         ->where('name', 'Evento Especial')->first();

            // Get all user IDs for the current team
            $userIds = $team->users->pluck('id');

            if ($workdayType && $userIds->isNotEmpty()) {
                DB::table('events')
                    ->whereIn('user_id', $userIds)
                    ->whereNull('event_type_id')
                    ->where(function ($query) {
                        $query->where('description', 'like', '%Jornada de trabajo%')
                              ->orWhere('description', 'like', '%Workday%');
                    })
                    ->update([
                        'event_type_id' => $workdayType->id,
                        'description' => $workdayType->name,
                    ]);
            }

            if ($specialEventType && $userIds->isNotEmpty()) {
                DB::table('events')
                    ->whereIn('user_id', $userIds)
                    ->whereNull('event_type_id')
                    ->where(function ($query) {
                        $query->where('description', 'like', '%Pausa%')
                              ->orWhere('description', 'like', '%Otros%');
                    })
                    ->update([
                        'event_type_id' => $specialEventType->id,
                        'description' => $specialEventType->name,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is a one-way data migration. Reversing it would require
        // storing the original descriptions, which is out of scope.
    }
};
