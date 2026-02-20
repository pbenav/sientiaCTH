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

        // Optimización: Extraer solo los atributos básicos si son modelos Eloquent
        // Esto evita que el JSON incluya todas las relaciones cargadas (eager loading) que inflan el tamaño
        $originalData = $original_event instanceof \Illuminate\Database\Eloquent\Model 
            ? $original_event->getAttributes() 
            : (is_array($original_event) ? $original_event : json_decode(json_encode($original_event), true));

        $modifiedData = $modified_event instanceof \Illuminate\Database\Eloquent\Model 
            ? $modified_event->getAttributes() 
            : (is_array($modified_event) ? $modified_event : json_decode(json_encode($modified_event), true));

        // Calcular la diferencia: solo almacenar lo que ha cambiado
        $finalOriginal = [];
        $finalModified = [];

        if (is_array($originalData) && is_array($modifiedData)) {
            foreach ($modifiedData as $key => $value) {
                // Si el campo no existía o ha cambiado, lo registramos
                if (!array_key_exists($key, $originalData) || $originalData[$key] != $value) {
                    $finalOriginal[$key] = $originalData[$key] ?? null;
                    $finalModified[$key] = $value;
                }
            }
            // También comprobamos si se han eliminado campos (aunque en este contexto es raro)
            foreach ($originalData as $key => $value) {
                if (!array_key_exists($key, $modifiedData)) {
                    $finalOriginal[$key] = $value;
                    $finalModified[$key] = null;
                }
            }
        }

        // Si no hay cambios reales, no insertamos nada
        if (empty($finalModified) && !$isAuthorizationChange) {
            return;
        }
        
        DB::table('events_history')->insert([
            'user_id' => auth()->user()->id,
            'tablename' => $tablename,
            'original_event' => json_encode($finalOriginal),
            'modified_event' => json_encode($finalModified),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
