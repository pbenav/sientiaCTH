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
            if (!Schema::hasColumn('work_centers', 'nfc_payload')) {
                $table->string('nfc_payload', 500)->nullable()->after('nfc_tag_description');
            }
            
            // Índice para búsquedas rápidas por payload - verificar si ya existe
            if (!$this->indexExists('work_centers', 'nfc_payload')) {
                $table->index('nfc_payload');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            if ($this->indexExists('work_centers', 'nfc_payload')) {
                $table->dropIndex(['nfc_payload']);
            }
            if (Schema::hasColumn('work_centers', 'nfc_payload')) {
                $table->dropColumn('nfc_payload');
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $column): bool
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);

        foreach ($indexes as $index) {
            if (in_array($column, $index->getColumns())) {
                return true;
            }
        }

        return false;
    }
};
