<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Check if events table exists
            if (!Schema::hasTable('events')) {
                Log::warning("Migration skipped: events table does not exist");
                return;
            }

            // Check if is_extra_hours column exists, if not, add it
            if (!Schema::hasColumn('events', 'is_extra_hours')) {
                Log::info("Adding is_extra_hours column to events table");
                
                Schema::table('events', function (Blueprint $table) {
                    $table->boolean('is_extra_hours')->default(false)->after('is_authorized');
                });
                
                Log::info("Added is_extra_hours column with default false");
            }

            // Ensure all existing events have a value for is_extra_hours (not NULL)
            $nullCount = DB::table('events')
                ->whereNull('is_extra_hours')
                ->count();

            if ($nullCount > 0) {
                Log::info("Setting default values for {$nullCount} events with NULL is_extra_hours");
                
                // Set default to false (not extra hours) for NULL values
                DB::table('events')
                    ->whereNull('is_extra_hours')
                    ->update(['is_extra_hours' => false]);
                    
                Log::info("Set default is_extra_hours = false for {$nullCount} events");
            }

            // Ensure the column has the correct default value and not null constraint
            if (Schema::hasColumn('events', 'is_extra_hours')) {
                // Get column info to check if it needs updating
                $columns = Schema::getColumnListing('events');
                
                if (in_array('is_extra_hours', $columns)) {
                    // Modify column to ensure it has proper default and not null
                    DB::statement('ALTER TABLE events MODIFY COLUMN is_extra_hours BOOLEAN NOT NULL DEFAULT FALSE');
                    Log::info("Ensured is_extra_hours column has proper default and NOT NULL constraint");
                }
            }

            Log::info("Migration completed: ensure_is_extra_hours_default_values");
            
        } catch (\Exception $e) {
            Log::error("Migration failed: ensure_is_extra_hours_default_values - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Log::info("Migration rollback: ensure_is_extra_hours_default_values");
            
            // This migration only ensures proper defaults, so rollback is minimal
            // We won't remove the column as it might be used by other parts of the system
            
            Log::info("Migration rollback completed (no changes needed)");
            
        } catch (\Exception $e) {
            Log::error("Migration rollback failed: ensure_is_extra_hours_default_values - " . $e->getMessage());
            throw $e;
        }
    }
};
