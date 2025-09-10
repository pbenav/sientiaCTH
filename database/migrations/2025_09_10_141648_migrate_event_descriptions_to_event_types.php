<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\EventType;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Find the event types by name. This assumes they have been seeded.
        $workdayType = EventType::where('name', 'Jornada laboral')->first();
        $specialEventType = EventType::where('name', 'Evento Especial')->first();

        if ($workdayType) {
            DB::table('events')
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

        if ($specialEventType) {
            DB::table('events')
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
