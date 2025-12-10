<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardStatsComponent extends Component
{
    public function render()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        
        // Get schedule for current day
        $schedule = $user->meta()
            ->where('meta_key', 'work_schedule')
            ->first();
        
        $currentSlot = null;
        $nextSlot = null;
        
        if ($schedule && $schedule->meta_value) {
            try {
                // Decode JSON, checking if it's valid
                $scheduleData = json_decode($schedule->meta_value, true);
                
                // Validate that decoding was successful and result is an array
                if (json_last_error() === JSON_ERROR_NONE && is_array($scheduleData)) {
                    $dayIso = (int) $now->format('N'); // ISO: 1=Monday, 7=Sunday
                    
                    // Schedule format: [{days: [1,2,3], start: "09:00", end: "14:00"}, ...]
                    foreach ($scheduleData as $slot) {
                        // Validate that slot is an array with required keys
                        if (!is_array($slot) || !isset($slot['days']) || !isset($slot['start']) || !isset($slot['end'])) {
                            continue; // Skip invalid slot
                        }
                        
                        // Check if current day is in this slot's days
                        if (!in_array($dayIso, $slot['days']) && !in_array((string)$dayIso, $slot['days'])) {
                            continue; // This slot is not for today
                        }
                        
                        try {
                            $slotStart = Carbon::parse($now->format('Y-m-d') . ' ' . $slot['start']);
                            $slotEnd = Carbon::parse($now->format('Y-m-d') . ' ' . $slot['end']);
                            
                            // Check if current time is within this slot
                            if ($now->between($slotStart, $slotEnd)) {
                                $currentSlot = $slot;
                            }
                            
                            // Find next slot for today
                            if (!$nextSlot && $slotStart->greaterThan($now)) {
                                $nextSlot = $slot;
                            }
                        } catch (\Exception $e) {
                            // Skip invalid time format
                            continue;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log error but continue gracefully
                \Log::warning('Failed to parse work schedule', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Calculate today's worked hours
        // Use user's timezone for accurate "today" filtering
        $userTimezone = config('app.timezone'); // or $user->timezone if you store it per user
        $todayStart = Carbon::now($userTimezone)->startOfDay();
        $todayEnd = Carbon::now($userTimezone)->endOfDay();
        
        $todayEvents = Event::where('user_id', $user->id)
            ->where('start', '>=', $todayStart)
            ->where('start', '<=', $todayEnd)
            ->get();
        
        $todaySeconds = 0;
        foreach ($todayEvents as $event) {
            // Parse with timezone to ensure correct interpretation
            $start = Carbon::parse($event->start, 'UTC')->setTimezone($userTimezone);
            
            if ($event->end) {
                $end = Carbon::parse($event->end, 'UTC')->setTimezone($userTimezone);
            } else {
                // If event is open, use current time
                $end = Carbon::now($userTimezone);
            }
            
            if ($end->greaterThan($start)) {
                $todaySeconds += $start->diffInSeconds($end);
            }
        }
        
        // Calculate this week's worked hours
        $weekStart = Carbon::now($userTimezone)->startOfWeek();
        $weekEnd = Carbon::now($userTimezone)->endOfDay();
        
        $weekEvents = Event::where('user_id', $user->id)
            ->where('start', '>=', $weekStart)
            ->where('start', '<=', $weekEnd)
            ->get();
        
        $weekSeconds = 0;
        foreach ($weekEvents as $event) {
            $start = Carbon::parse($event->start, 'UTC')->setTimezone($userTimezone);
            
            if ($event->end) {
                $end = Carbon::parse($event->end, 'UTC')->setTimezone($userTimezone);
            } else {
                // If event is open, use current time
                $end = Carbon::now($userTimezone);
            }
            
            if ($end->greaterThan($start)) {
                $weekSeconds += $start->diffInSeconds($end);
            }
        }
        
        // Calculate this year's worked days
        $yearStart = Carbon::now($userTimezone)->startOfYear();
        $yearEnd = Carbon::now($userTimezone)->endOfDay();
        
        $yearDays = Event::where('user_id', $user->id)
            ->where('start', '>=', $yearStart)
            ->where('start', '<=', $yearEnd)
            ->selectRaw('DATE(CONVERT_TZ(start, "+00:00", "' . $userTimezone . '")) as work_date')
            ->groupBy('work_date')
            ->get()
            ->count();
            
        // Helper to format seconds to H:i:s
        $formatSeconds = function($seconds) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        };
        
        return view('livewire.dashboard-stats-component', [
            'currentSlot' => $currentSlot,
            'nextSlot' => $nextSlot,
            'todayHours' => $formatSeconds($todaySeconds),
            'weekHours' => $formatSeconds($weekSeconds),
            'yearDays' => $yearDays,
        ]);
    }
}
