<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Provides a method for inserting records into the events history table.
 *
 * This trait is used to log changes to events, providing an audit trail of
 * modifications.
 */
trait InsertHistory
{
    /**
     * Insert a new record into the events history table.
     *
     * @param string $tablename The name of the table that was modified.
     * @param mixed $original_event The original event data.
     * @param mixed $modified_event The modified event data.
     * @return void
     */
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
