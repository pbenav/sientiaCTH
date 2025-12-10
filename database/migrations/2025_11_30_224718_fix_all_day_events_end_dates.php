<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes all-day events that were stored with end date at 00:00 of the next day.
     * We adjust them to end at 23:59:59 of the same day to ensure they appear only once in statistics.
     */
    public function up(): void
    {
        // Get all events that are all-day type
        $allDayEvents = DB::table('events')
            ->join('event_types', 'events.event_type_id', '=', 'event_types.id')
            ->where('event_types.is_all_day', true)
            ->whereNotNull('events.end')
            ->select('events.id', 'events.start', 'events.end')
            ->get();

        foreach ($allDayEvents as $event) {
            $start = Carbon::parse($event->start, 'UTC');
            $end = Carbon::parse($event->end, 'UTC');
            
            // Check if end is at 00:00:00 (midnight)
            if ($end->format('H:i:s') === '00:00:00') {
                // Set end to 23:59:59 of the previous day (which is the actual event day)
                $newEnd = $end->copy()->subDay()->endOfDay();
                
                DB::table('events')
                    ->where('id', $event->id)
                    ->update(['end' => $newEnd]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * 
     * This restores the original format (end at 00:00 of next day)
     */
    public function down(): void
    {
        // Get all events that are all-day type
        $allDayEvents = DB::table('events')
            ->join('event_types', 'events.event_type_id', '=', 'event_types.id')
            ->where('event_types.is_all_day', true)
            ->whereNotNull('events.end')
            ->select('events.id', 'events.start', 'events.end')
            ->get();

        foreach ($allDayEvents as $event) {
            $start = Carbon::parse($event->start, 'UTC');
            $end = Carbon::parse($event->end, 'UTC');
            
            // Check if end is at 23:59:59 (end of day)
            if ($end->format('H:i:s') === '23:59:59') {
                // Set end back to 00:00:00 of the next day
                $newEnd = $end->copy()->addSecond()->startOfDay();
                
                DB::table('events')
                    ->where('id', $event->id)
                    ->update(['end' => $newEnd]);
            }
        }
    }
};
