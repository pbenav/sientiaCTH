<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration normalizes work schedule days in user_meta table:
     * - Converts Spanish day abbreviations (L, M, X, J, V, S, D) to ISO numbers (1-7)
     * - Removes duplicates
     * - Sorts days in ascending order (1-7)
     * 
     * This migration is idempotent and can be run multiple times safely.
     */
    public function up(): void
    {
        // Day mapping from Spanish abbreviations to ISO numbers
        $dayMap = [
            'L' => 1, // Lunes
            'M' => 2, // Martes
            'X' => 3, // Miércoles
            'J' => 4, // Jueves
            'V' => 5, // Viernes
            'S' => 6, // Sábado
            'D' => 7, // Domingo
        ];

        // Get all work_schedule entries from user_meta
        $schedules = DB::table('user_meta')
            ->where('meta_key', 'work_schedule')
            ->get();

        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($schedules as $scheduleMeta) {
            try {
                $schedule = json_decode($scheduleMeta->meta_value, true);

                // Skip if JSON is invalid
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errorCount++;
                    continue;
                }

                // Skip if not an array
                if (!is_array($schedule)) {
                    $skippedCount++;
                    continue;
                }

                $modified = false;

                foreach ($schedule as &$slot) {
                    // Skip if slot is not an array
                    if (!is_array($slot)) {
                        continue;
                    }

                    // Process 'days' field if it exists
                    if (isset($slot['days']) && is_array($slot['days'])) {
                        $newDays = [];
                        
                        foreach ($slot['days'] as $day) {
                            // If it's a number (already ISO format)
                            if (is_numeric($day)) {
                                $dayInt = (int)$day;
                                if ($dayInt >= 1 && $dayInt <= 7) {
                                    $newDays[] = $dayInt;
                                }
                            }
                            // If it's a letter (Spanish abbreviation)
                            elseif (is_string($day)) {
                                $dayUpper = strtoupper(trim($day));
                                if (isset($dayMap[$dayUpper])) {
                                    $newDays[] = $dayMap[$dayUpper];
                                    $modified = true;
                                }
                            }
                        }

                        // Remove duplicates and sort
                        $newDays = array_unique($newDays);
                        sort($newDays);
                        
                        // Check if the sorted array is different from original
                        if ($slot['days'] !== $newDays) {
                            $modified = true;
                        }
                        
                        $slot['days'] = array_values($newDays); // Re-index array
                    }
                }

                // Only update if changes were made
                if ($modified) {
                    $newJson = json_encode($schedule);
                    
                    // Skip if JSON encoding failed
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errorCount++;
                        continue;
                    }

                    DB::table('user_meta')
                        ->where('id', $scheduleMeta->id)
                        ->update(['meta_value' => $newJson]);
                    
                    $processedCount++;
                } else {
                    $skippedCount++;
                }

            } catch (\Exception $e) {
                $errorCount++;
                // Log error but continue processing
                \Log::error("Error processing schedule for user_meta id {$scheduleMeta->id}: " . $e->getMessage());
            }
        }

        // Log summary
        \Log::info("Work schedule normalization completed: {$processedCount} updated, {$skippedCount} skipped, {$errorCount} errors");
    }

    /**
     * Reverse the migrations.
     * 
     * This migration is data transformation only, so we don't reverse it.
     * Re-running the migration is safe and idempotent.
     */
    public function down(): void
    {
        // No rollback needed - this is a data normalization migration
        // The data transformation is idempotent and doesn't need to be reversed
    }
};
