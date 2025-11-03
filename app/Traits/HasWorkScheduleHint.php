<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Provides a method for generating a work schedule hint.
 *
 * This trait is used in Livewire components to provide users with a helpful
 * hint about their work schedule for the current day.
 */
trait HasWorkScheduleHint
{
    /**
     * Sets a work schedule hint based on the user's defined schedule.
     *
     * This method analyzes the user's work schedule and determines the most
     * relevant time slot for the current time. It then sets a
     * `workScheduleHint` property on the component with a descriptive
     * message. If the component has an open event, it may also suggest an end
     * time.
     *
     * @return void
     */
    public function setWorkScheduleHint()
    {
        if (!Auth::check()) {
            $this->workScheduleHint = '';
            return;
        }

        $user = Auth::user();
        $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();

        if (!$workScheduleMeta) {
            $this->workScheduleHint = 'No work schedule defined.';
            return;
        }

        $schedule = json_decode($workScheduleMeta->meta_value, true);
        if (empty($schedule)) {
            $this->workScheduleHint = 'The work schedule format is incorrect.';
            return;
        }

        $now = new \DateTime();
        $currentTime = $now->format('H:i:s');
        $currentDay = $now->format('N'); // 1 (Monday) to 7 (Sunday)
        $dayMap = ['L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7];

        $todaysSlots = [];
        foreach ($schedule as $slot) {
            $slotDays = $slot['days'] ?? [];
            foreach ($slotDays as $day) {
                if (isset($dayMap[$day]) && $dayMap[$day] == $currentDay) {
                    $todaysSlots[] = $slot;
                    break;
                }
            }
        }

        if (empty($todaysSlots)) {
            $this->workScheduleHint = 'No slots for today.';
            return;
        }

        // Sort today's slots by start time
        usort($todaysSlots, function ($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        $currentSlot = null;
        $lastFinishedSlot = null;

        foreach ($todaysSlots as $slot) {
            if ($currentTime >= $slot['start'] && $currentTime <= $slot['end']) {
                $currentSlot = $slot;
                break; // Found the current slot
            }
            if ($currentTime > $slot['end']) {
                $lastFinishedSlot = $slot; // This might be the one we are looking for
            }
        }

        $relevantSlot = $currentSlot ?? $lastFinishedSlot;

        if ($relevantSlot) {
            if ($currentSlot) {
                $this->workScheduleHint = __('Suggested current slot') . ": {$relevantSlot['start']} - {$relevantSlot['end']}";
            } else {
                $this->workScheduleHint = __('Last finished slot') . ": {$relevantSlot['start']} - {$relevantSlot['end']}";
            }

            // This part will only execute if the component has an 'event' property
            if (property_exists($this, 'event') && $this->event && $this->event->is_open == 1) {
                $this->event->end = date('Y-m-d') . ' ' . $relevantSlot['end'];
            }
        } else {
            $this->workScheduleHint = __('No applicable time slot found');
        }
    }
}
