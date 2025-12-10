<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asignar team_id a eventos legacy basándose en el current_team_id del usuario
        // Sintaxis compatible con MySQL/MariaDB y PostgreSQL
        DB::statement('
            UPDATE events 
            INNER JOIN users ON events.user_id = users.id
            SET events.team_id = users.current_team_id
            WHERE events.team_id IS NULL
            AND users.current_team_id IS NOT NULL
        ');
        
        // Log de eventos actualizados
        $updated = DB::table('events')->whereNull('team_id')->count();
        if ($updated > 0) {
            \Log::warning("Quedan {$updated} eventos sin team_id (usuarios sin equipo asignado)");
        }
        
        $total = DB::table('events')->whereNotNull('team_id')->count();
        \Log::info("Migración completada: {$total} eventos tienen team_id asignado");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir - mantener los team_id asignados
        \Log::info('Rollback de assign_team_id_to_legacy_events - No se revierten cambios por seguridad');
    }
};
