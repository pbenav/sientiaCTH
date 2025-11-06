<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\EventType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // Check if event_types table exists
            if (!Schema::hasTable('event_types')) {
                Log::warning("Migration skipped: event_types table does not exist");
                return;
            }

            // Check if is_workday_type column exists
            if (!Schema::hasColumn('event_types', 'is_workday_type')) {
                Log::warning("Migration skipped: is_workday_type column does not exist in event_types table");
                return;
            }

            // Check if name column exists
            if (!Schema::hasColumn('event_types', 'name')) {
                Log::warning("Migration skipped: name column does not exist in event_types table");
                return;
            }

            // Count events before update to avoid unnecessary operations
            $toUpdate = EventType::where('name', 'Jornada laboral')
                ->where('is_workday_type', false)
                ->count();

            if ($toUpdate === 0) {
                Log::info("Migration: No 'Jornada laboral' event types need updating - all are already workday types");
                return;
            }

            // Update all "Jornada laboral" event types to be workday types
            // This ensures consistent behavior across all teams
            $updated = EventType::where('name', 'Jornada laboral')
                ->where('is_workday_type', false)
                ->update(['is_workday_type' => true]);
            
            Log::info("Migration: Updated {$updated} 'Jornada laboral' event types to be workday types");
            
        } catch (\Exception $e) {
            Log::error("Migration failed: update_workday_types_for_all_teams - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // Check if event_types table exists
            if (!Schema::hasTable('event_types')) {
                Log::warning("Migration rollback skipped: event_types table does not exist");
                return;
            }

            // Check if required columns exist
            if (!Schema::hasColumn('event_types', 'is_workday_type') || 
                !Schema::hasColumn('event_types', 'name') || 
                !Schema::hasColumn('event_types', 'team_id')) {
                Log::warning("Migration rollback skipped: required columns do not exist in event_types table");
                return;
            }

            // Count events before rollback
            $toRevert = EventType::where('name', 'Jornada laboral')
                ->where('team_id', '>', 1)
                ->where('is_workday_type', true)
                ->count();

            if ($toRevert === 0) {
                Log::info("Migration rollback: No 'Jornada laboral' event types need reverting");
                return;
            }

            // Revert only non-first team event types (keep team 1 as workday type)
            // This preserves the original state where only team 1 had workday type = true
            $reverted = EventType::where('name', 'Jornada laboral')
                ->where('team_id', '>', 1)
                ->update(['is_workday_type' => false]);
                
            Log::info("Migration rollback: Reverted {$reverted} 'Jornada laboral' event types for teams > 1 to non-workday types");
            
        } catch (\Exception $e) {
            Log::error("Migration rollback failed: update_workday_types_for_all_teams - " . $e->getMessage());
            throw $e;
        }
    }
};
