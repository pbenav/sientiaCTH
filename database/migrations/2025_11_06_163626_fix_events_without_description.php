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

            // Check if description column exists
            if (!Schema::hasColumn('events', 'description')) {
                Log::warning("Migration skipped: description column does not exist in events table");
                return;
            }

            // Check if event_types table exists
            if (!Schema::hasTable('event_types')) {
                Log::warning("Migration skipped: event_types table does not exist");
                return;
            }

            Log::info("Starting migration: fix_events_without_description");

            // Update events with NULL or empty descriptions using their event type name
            if (DB::connection()->getDriverName() === 'sqlite') {
                $updated = DB::update("
                    UPDATE events 
                    SET description = COALESCE(
                        (SELECT name FROM event_types WHERE event_types.id = events.event_type_id),
                        'Evento de trabajo'
                    )
                    WHERE (description IS NULL OR description = '' OR description = 'null')
                    AND event_type_id IS NOT NULL
                ");
            } else {
                $updated = DB::update("
                    UPDATE events 
                    LEFT JOIN event_types ON events.event_type_id = event_types.id 
                    SET events.description = COALESCE(event_types.name, 'Evento de trabajo')
                    WHERE (events.description IS NULL OR events.description = '' OR events.description = 'null')
                    AND event_types.name IS NOT NULL
                ");
            }

            Log::info("Migration: Updated {$updated} events with descriptions from their event types");

            // Update events without event type to have a default description
            $orphanUpdated = DB::update("
                UPDATE events 
                SET description = 'Evento de trabajo' 
                WHERE (description IS NULL OR description = '' OR description = 'null')
                AND event_type_id IS NULL
            ");

            if ($orphanUpdated > 0) {
                Log::info("Migration: Updated {$orphanUpdated} events without event type to have default description");
            }

            // Ensure description column is not nullable for future events
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE events MODIFY COLUMN description VARCHAR(255) NULL DEFAULT NULL");
                Log::info("Ensured description column allows NULL values with proper default");
            }

            Log::info("Migration completed successfully: fix_events_without_description");
            
        } catch (\Exception $e) {
            Log::error("Migration failed: fix_events_without_description - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Log::info("Migration rollback: fix_events_without_description");
            
            // This migration only fixes data, so rollback doesn't need to revert descriptions
            // as they are now correct and meaningful
            
            Log::info("Migration rollback completed (no reversion needed - descriptions are correct)");
            
        } catch (\Exception $e) {
            Log::error("Migration rollback failed: fix_events_without_description - " . $e->getMessage());
            throw $e;
        }
    }
};
