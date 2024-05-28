<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait InsertHistory
{
    public function insertHistory($tablename, $original_event, $modified_event)
    {
        DB::table('events_history')->insert([
            'user_id' => auth()->user()->id,
            'tablename' => $tablename,
            'original_event' => $original_event->toJson(),
            'modified_event' => $modified_event->toJson(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
