<?php

namespace App\Traits;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Provides methods for authorizing event-related actions.
 *
 * This trait contains the logic for checking if a user is permitted to
 * modify an event and for verifying if a given time falls within a user's
 * defined work schedule.
 */
trait HandlesEventAuthorization
{
    /**
     * Determine if the current user can modify a given event.
     *
     * @param Event $event
     * @return bool
     */
    public function canModifyEvent(Event $event)
    {
        $user = Auth::user();
        
        if (!$user || !$user->currentTeam) {
            \Log::info('canModifyEvent: No user or no currentTeam', ['user_id' => $user?->id]);
            return false;
        }

        // Check admin status FIRST (before inspector check)
        // Admins can always modify events (including team owners and global admins).
        $team = $user->currentTeam;
        $isAdmin = $user->hasTeamRole($team, 'admin') || $user->ownsTeam($team) || $user->is_admin;
        \Log::info('canModifyEvent: Checking roles', [
            'user_id' => $user->id, 
            'isAdmin' => $isAdmin, 
            'isInspector' => $user->isInspector(),
            'hasTeamRole' => $user->hasTeamRole($team, 'admin'),
            'ownsTeam' => $user->ownsTeam($team),
            'is_global_admin' => $user->is_admin,
            'event_is_open' => $event->is_open, 
            'event_user_id' => $event->user_id
        ]);
        
        if ($isAdmin) {
            return true;
        }

        // Inspectors (who are NOT admins) can never modify events
        if ($user->isInspector()) {
            \Log::info('canModifyEvent: User is inspector only (not admin)', ['user_id' => $user->id]);
            return false;
        }

        // Users can modify their own open events
        if ($event->user_id == $user->id && $event->is_open) {
            \Log::info('canModifyEvent: User owns the event and it is open', ['user_id' => $user->id, 'event_id' => $event->id]);
            return true;
        }

        // Non-admins can only modify open events (fallback for backward compatibility)
        return $event->is_open;
    }

    /**
     * Check if a given time is within the user's work schedule.
     *
     * @param Carbon $timeToCheck
     * @param User|null $user Optional user to check. If null, uses Auth::user()
     * @return bool
     * @uses \Carbon\Carbon
     */
    public function isWithinWorkSchedule(Carbon $timeToCheck, $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user || !$user->currentTeam) {
            return false;
        }
        
        $team = $user->currentTeam;
        $workScheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();

        if (!$workScheduleMeta || !$team || empty(json_decode($workScheduleMeta->meta_value, true))) {
            return true;
        }

        $workSchedule = json_decode($workScheduleMeta->meta_value, true);

        $delayMinutes = $team->clock_in_delay_minutes ?? 0;

        $dayOfWeek = $timeToCheck->format('N');
        
        // Helper function to normalize day to ISO number (1-7)
        $normalizeDayToISO = function($day) {
            // If it's already a number between 1-7, return it
            if (is_numeric($day) && $day >= 1 && $day <= 7) {
                return (int)$day;
            }
            
            // Spanish abbreviations
            $dayMap = ['L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7];
            if (isset($dayMap[$day])) {
                return $dayMap[$day];
            }
            
            // Full day names in English
            $englishDays = [
                'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4,
                'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7
            ];
            if (isset($englishDays[$day])) {
                return $englishDays[$day];
            }
            
            // Full day names in Spanish
            $spanishDays = [
                'Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4,
                'Viernes' => 5, 'Sábado' => 6, 'Domingo' => 7
            ];
            if (isset($spanishDays[$day])) {
                return $spanishDays[$day];
            }
            
            return null;
        };

        $isWithinAnySlot = false;
        foreach ($workSchedule as $slot) {
            if (isset($slot['days']) && isset($slot['start']) && isset($slot['end'])) {
                // Check if today is in the slot days
                $isTodayInSlot = false;
                foreach ($slot['days'] as $day) {
                    $normalizedDay = $normalizeDayToISO($day);
                    if ($normalizedDay !== null && $normalizedDay == $dayOfWeek) {
                        $isTodayInSlot = true;
                        break;
                    }
                }

                if ($isTodayInSlot) {
                    $startTime = Carbon::parse($timeToCheck->format('Y-m-d') . ' ' . $slot['start']);
                    $endTime = Carbon::parse($timeToCheck->format('Y-m-d') . ' ' . $slot['end']);

                    if ($endTime->lessThan($startTime)) {
                        $endTime->addDay();
                    }

                    $startTimeWithGrace = $startTime->copy()->subMinutes($delayMinutes);
                    $endTimeWithGrace = $endTime->copy()->addMinutes($delayMinutes);

                    if ($timeToCheck->between($startTimeWithGrace, $endTimeWithGrace)) {
                        $isWithinAnySlot = true;
                        break;
                    }
                }
            }
        }

        if (!$team->force_clock_in_delay) {
            return $isWithinAnySlot;
        }

        return $isWithinAnySlot;
    }

    /**
     * Backward compatibility wrapper for isWithinWorkSchedule
     * @deprecated Use isWithinWorkSchedule instead
     */
    public function isUserWithinWorkSchedule($timeToCheck, $user = null)
    {
        return $this->isWithinWorkSchedule($timeToCheck, $user);
    }

    /**
     * Check if a given time is within the allowed entry window of any work slot.
     *
     * @param Carbon $timeToCheck
     * @param User|null $user Optional user to check. If null, uses Auth::user()
     * @return bool
     */
    public function isWithinEntryWindow(Carbon $timeToCheck, $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user || !$user->currentTeam) {
            return false;
        }
        
        $team = $user->currentTeam;
        
        // If delay is not forced, we fallback to standard schedule check
        if (!$team->force_clock_in_delay) {
            return $this->isWithinWorkSchedule($timeToCheck, $user);
        }

        $workScheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();

        if (!$workScheduleMeta || empty(json_decode($workScheduleMeta->meta_value, true))) {
            return true;
        }

        $workSchedule = json_decode($workScheduleMeta->meta_value, true);
        $delayMinutes = $team->clock_in_delay_minutes ?? 0;
        
        // Get the timezone from the Carbon object that was passed
        $timezone = $timeToCheck->timezone->getName();
        $dayOfWeek = $timeToCheck->format('N');
        
        // Helper function to normalize day to ISO number (1-7)
        $normalizeDayToISO = function($day) {
            if (is_numeric($day) && $day >= 1 && $day <= 7) return (int)$day;
            $dayMap = ['L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7];
            if (isset($dayMap[$day])) return $dayMap[$day];
            $englishDays = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
            if (isset($englishDays[$day])) return $englishDays[$day];
            $spanishDays = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6, 'Domingo' => 7];
            if (isset($spanishDays[$day])) return $spanishDays[$day];
            return null;
        };

        foreach ($workSchedule as $slot) {
            if (isset($slot['days']) && isset($slot['start'])) {
                // Check current day
                $isTodayInSlot = false;
                foreach ($slot['days'] as $day) {
                    $normalizedDay = $normalizeDayToISO($day);
                    if ($normalizedDay !== null && $normalizedDay == $dayOfWeek) {
                        $isTodayInSlot = true;
                        break;
                    }
                }

                if ($isTodayInSlot) {
                    $startTime = Carbon::parse($timeToCheck->format('Y-m-d') . ' ' . $slot['start'], $timezone);
                    
                    // If the slot has an end time, check ONLY entry and exit windows
                    if (isset($slot['end'])) {
                        $endTime = Carbon::parse($timeToCheck->format('Y-m-d') . ' ' . $slot['end'], $timezone);
                        
                        // Handle overnight shifts
                        if ($endTime->lessThan($startTime)) {
                            $endTime->addDay();
                        }
                        
                        // Define entry window: start - delay to start + delay
                        $entryWindowStart = $startTime->copy()->subMinutes($delayMinutes);
                        $entryWindowEnd = $startTime->copy()->addMinutes($delayMinutes);
                        
                        // Define exit window: end - delay to end + delay
                        $exitWindowStart = $endTime->copy()->subMinutes($delayMinutes);
                        $exitWindowEnd = $endTime->copy()->addMinutes($delayMinutes);
                        
                        // Check if we're within EITHER the entry window OR the exit window
                        if ($timeToCheck->between($entryWindowStart, $entryWindowEnd) ||
                            $timeToCheck->between($exitWindowStart, $exitWindowEnd)) {
                            return true;
                        }
                    } else {
                        // If no end time, only check entry window around start time
                        // Entry window: StartTime - Delay to StartTime + Delay
                        $windowStart = $startTime->copy()->subMinutes($delayMinutes);
                        $windowEnd = $startTime->copy()->addMinutes($delayMinutes);

                        if ($timeToCheck->between($windowStart, $windowEnd)) {
                            return true;
                        }
                    }
                }
                
                // For overnight shifts, also check if we're in the continuation from yesterday
                if (isset($slot['end'])) {
                    $yesterdayDay = $timeToCheck->copy()->subDay()->format('N');
                    $isYesterdayInSlot = false;
                    
                    foreach ($slot['days'] as $day) {
                        $normalizedDay = $normalizeDayToISO($day);
                        if ($normalizedDay !== null && $normalizedDay == $yesterdayDay) {
                            $isYesterdayInSlot = true;
                            break;
                        }
                    }
                    
                    if ($isYesterdayInSlot) {
                        $yesterdayStart = Carbon::parse($timeToCheck->copy()->subDay()->format('Y-m-d') . ' ' . $slot['start'], $timezone);
                        $yesterdayEnd = Carbon::parse($timeToCheck->copy()->subDay()->format('Y-m-d') . ' ' . $slot['end'], $timezone);
                        
                        // If end is less than start, it's an overnight shift
                        if ($yesterdayEnd->lessThan($yesterdayStart)) {
                            $yesterdayEnd->addDay(); // This brings it to today
                            
                            // Define exit window for the overnight shift: end - delay to end + delay
                            $exitWindowStart = $yesterdayEnd->copy()->subMinutes($delayMinutes);
                            $exitWindowEnd = $yesterdayEnd->copy()->addMinutes($delayMinutes);
                            
                            // Check if current time is within the exit window of yesterday's overnight shift
                            if ($timeToCheck->between($exitWindowStart, $exitWindowEnd)) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
