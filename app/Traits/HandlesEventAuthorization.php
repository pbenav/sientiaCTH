<?php

namespace App\Traits;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

        // Admins can always modify events.
        if ($user->hasTeamRole($user->currentTeam, 'admin')) {
            return true;
        }

        // Non-admins can only modify open events.
        return $event->is_open;
    }

    /**
     * Check if a given time is within the user's work schedule.
     *
     * @param Carbon $timeToCheck
     * @return bool
     */
    public function isWithinWorkSchedule(Carbon $timeToCheck)
    {
        $user = Auth::user();
        $workScheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();

        if (!$workScheduleMeta) {
            return false; // No schedule defined, so they are always "outside".
        }

        $workSchedule = json_decode($workScheduleMeta->meta_value, true);
        if (empty($workSchedule)) {
            return false;
        }

        $dayOfWeek = $timeToCheck->format('N');
        $dayMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
        $currentDayLetter = $dayMap[$dayOfWeek];

        foreach ($workSchedule as $slot) {
            // Check if the day is one of the days for this slot and if the slot has start/end times
            if (isset($slot['days']) && in_array($currentDayLetter, $slot['days']) && isset($slot['start']) && isset($slot['end'])) {
                $startTime = Carbon::parse($timeToCheck->format('Y-m-d') . ' ' . $slot['start']);
                $endTime = Carbon::parse($timeToCheck->format('Y-m-d') . ' ' . $slot['end']);

                if ($endTime->lessThan($startTime)) {
                    $endTime->addDay();
                }

                if ($timeToCheck->between($startTime, $endTime)) {
                    return true;
                }
            }
        }

        return false;
    }
}
