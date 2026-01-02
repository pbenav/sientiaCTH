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
     * Solo registra auditoría si:
     * 1. El evento está cerrado (is_open = false)
     * 2. O es un cambio de autorización (independiente del estado)
     *
     * @param string $tablename The name of the table that was modified.
     * @param mixed $original_event The original event data.
     * @param mixed $modified_event The modified event data.
     * @param bool $isAuthorizationChange Indica si es un cambio de autorización
     * @return void
     */
    public function insertHistory($tablename, $original_event, $modified_event, $isAuthorizationChange = false)
    {
        // Solo auditar si el evento está cerrado o es un cambio de autorización
        $shouldAudit = false;
        
        if ($isAuthorizationChange) {
            // Siempre auditar cambios de autorización
            $shouldAudit = true;
        } elseif (isset($modified_event->is_open) && $modified_event->is_open === false) {
            // Auditar solo eventos cerrados
            $shouldAudit = true;
        }
        
        if (!$shouldAudit) {
            return;
        }
        
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
