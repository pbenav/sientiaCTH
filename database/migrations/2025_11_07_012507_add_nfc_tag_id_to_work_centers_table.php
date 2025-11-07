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
            if (!Schema::hasColumn('work_centers', 'nfc_tag_id')) {
                $table->string('nfc_tag_id', 64)->nullable()->unique()->after('code');
            }
            if (!Schema::hasColumn('work_centers', 'nfc_tag_description')) {
                $table->text('nfc_tag_description')->nullable()->after('nfc_tag_id');
            }
            if (!Schema::hasColumn('work_centers', 'nfc_tag_generated_at')) {
                $table->timestamp('nfc_tag_generated_at')->nullable()->after('nfc_tag_description');
            }
            
            // Índices para optimizar búsquedas - verificar si ya existen
            if (!$this->indexExists('work_centers', 'nfc_tag_id')) {
                $table->index('nfc_tag_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            if ($this->indexExists('work_centers', 'nfc_tag_id')) {
                $table->dropIndex(['nfc_tag_id']);
            }
            if (Schema::hasColumn('work_centers', 'nfc_tag_id')) {
                $table->dropColumn(['nfc_tag_id', 'nfc_tag_description', 'nfc_tag_generated_at']);
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
