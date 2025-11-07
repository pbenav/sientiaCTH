<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            // Agregar campo para almacenar el payload completo del NFC
            // que incluirá la URL del servidor + el ID del centro de trabajo
            // Usamos VARCHAR en lugar de TEXT para poder indexar
            $table->string('nfc_payload', 500)->nullable()->after('nfc_tag_description');
            
            // Índice para búsquedas rápidas por payload
            $table->index('nfc_payload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            $table->dropIndex(['nfc_payload']);
            $table->dropColumn('nfc_payload');
        });
    }
};
