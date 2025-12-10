<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change team_id foreign key in events table from CASCADE to SET NULL
     * to preserve historical records when teams are deleted.
     */
    public function up(): void
    {
        // Check if the table and column exist
        if (!Schema::hasTable('events') || !Schema::hasColumn('events', 'team_id')) {
            return;
        }

        // Get existing foreign key name
        $foreignKeys = $this->getForeignKeys('events', 'team_id');
        
        Schema::table('events', function (Blueprint $table) use ($foreignKeys) {
            // Drop existing foreign key constraint if exists
            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk);
            }
            
            // Recreate with SET NULL on delete
            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('events') || !Schema::hasColumn('events', 'team_id')) {
            return;
        }

        $foreignKeys = $this->getForeignKeys('events', 'team_id');
        
        Schema::table('events', function (Blueprint $table) use ($foreignKeys) {
            // Drop the SET NULL constraint
            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk);
            }
            
            // Restore original CASCADE behavior
            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('cascade');
        });
    }

    /**
     * Get foreign key names for a specific column
     */
    private function getForeignKeys(string $table, string $column): array
    {
        $databaseName = DB::getDatabaseName();
        
        return DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND COLUMN_NAME = ? 
             AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$databaseName, $table, $column]
        ) ? array_column(
            DB::select(
                "SELECT CONSTRAINT_NAME 
                 FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = ? 
                 AND TABLE_NAME = ? 
                 AND COLUMN_NAME = ? 
                 AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$databaseName, $table, $column]
            ), 
            'CONSTRAINT_NAME'
        ) : [];
    }
};
