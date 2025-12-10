<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Event;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix all-day events that were stored with incorrect timezone conversions.
     * All-day events should be stored as pure dates in UTC (YYYY-MM-DD 00:00:00)
     * without timezone offset adjustments.
     */
    public function up(): void
    {
        // Get all events that have an event type marked as all-day
        $allDayEvents = Event::whereHas('eventType', function ($query) {
            $query->where('is_all_day', true);
        })->get();

        foreach ($allDayEvents as $event) {
            // Parse the current start and end dates
            // These may have been stored with timezone offsets (e.g., 23:00:00 UTC instead of 00:00:00)
            $startCarbon = Carbon::parse($event->start, 'UTC');
            
            // For all-day events, we want to extract just the date and set it to midnight UTC
            // If the time is 23:00:00, this will round to the next day at midnight
            // If the time is 00:00:00, it will stay the same
            $correctedStart = $startCarbon->startOfDay()->format('Y-m-d H:i:s');
            
            $correctedEnd = null;
            if ($event->end) {
                $endCarbon = Carbon::parse($event->end, 'UTC');
                // The end date should also be at midnight UTC
                $correctedEnd = $endCarbon->startOfDay()->format('Y-m-d H:i:s');
            }
            
            // Update the event with corrected dates
            $event->update([
                'start' => $correctedStart,
                'end' => $correctedEnd,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes data, so there's no meaningful way to reverse it
        // The old incorrect data is lost after the migration runs
    }
};
