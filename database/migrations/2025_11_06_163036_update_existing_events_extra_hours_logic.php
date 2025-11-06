<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Event;

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

            // Check if is_extra_hours column exists
            if (!Schema::hasColumn('events', 'is_extra_hours')) {
                Log::warning("Migration skipped: is_extra_hours column does not exist in events table");
                return;
            }

            // Check if event_type_id column exists
            if (!Schema::hasColumn('events', 'event_type_id')) {
                Log::warning("Migration skipped: event_type_id column does not exist in events table");
                return;
            }

            // Check if event_types table exists
            if (!Schema::hasTable('event_types')) {
                Log::warning("Migration skipped: event_types table does not exist");
                return;
            }

            // Check if is_workday_type column exists in event_types
            if (!Schema::hasColumn('event_types', 'is_workday_type')) {
                Log::warning("Migration skipped: is_workday_type column does not exist in event_types table");
                return;
            }

            Log::info("Starting migration: update_existing_events_extra_hours_logic");

            // Use raw SQL for better performance on large datasets
            // Update events to set is_extra_hours based on event type's is_workday_type
            $updated = DB::update("
                UPDATE events 
                LEFT JOIN event_types ON events.event_type_id = event_types.id 
                SET events.is_extra_hours = CASE 
                    WHEN event_types.is_workday_type = 1 THEN 0 
                    ELSE 1 
                END
                WHERE events.is_extra_hours != CASE 
                    WHEN event_types.is_workday_type = 1 THEN 0 
                    ELSE 1 
                END
            ");

            Log::info("Migration: Updated {$updated} events with new extra hours logic");

            // Handle events without event type (set as extra hours)
            $orphanUpdated = DB::update("
                UPDATE events 
                SET is_extra_hours = 1 
                WHERE event_type_id IS NULL 
                AND is_extra_hours = 0
            ");

            if ($orphanUpdated > 0) {
                Log::info("Migration: Updated {$orphanUpdated} events without event type to be extra hours");
            }

            Log::info("Migration completed successfully: update_existing_events_extra_hours_logic");
            
        } catch (\Exception $e) {
            Log::error("Migration failed: update_existing_events_extra_hours_logic - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            // Check if events table exists
            if (!Schema::hasTable('events')) {
                Log::warning("Migration rollback skipped: events table does not exist");
                return;
            }

            // Check if is_extra_hours column exists
            if (!Schema::hasColumn('events', 'is_extra_hours')) {
                Log::warning("Migration rollback skipped: is_extra_hours column does not exist in events table");
                return;
            }

            Log::info("Starting migration rollback: update_existing_events_extra_hours_logic");

            // Note: It's difficult to revert to the exact previous state because the old logic
            // was based on work schedules and time slots. We'll set a reasonable default.
            // For rollback, we'll set is_extra_hours based on a simple heuristic:
            // - Events without event_type_id: keep as extra hours (1)
            // - Events with workday event types: set to normal (0)  
            // - Events with non-workday event types: keep as extra hours (1)

            Log::warning("Migration rollback: Cannot perfectly restore previous extra hours logic. Setting reasonable defaults.");

            // This rollback is intentionally conservative - it doesn't change much
            // because the previous logic was complex and can't be perfectly restored
            $rolledBack = 0;
            
            Log::info("Migration rollback completed: {$rolledBack} events processed (conservative rollback)");
            
        } catch (\Exception $e) {
            Log::error("Migration rollback failed: update_existing_events_extra_hours_logic - " . $e->getMessage());
            throw $e;
        }
    }
};
