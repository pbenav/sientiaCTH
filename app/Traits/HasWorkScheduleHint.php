<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasWorkScheduleHint
{
    /**
     * Sets a work schedule hint based on the user's defined schedule.
     * If the event is open, it may also suggest an end time.
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
            $this->workScheduleHint = 'No hay horario laboral definido.';
            return;
        }

        $schedule = json_decode($workScheduleMeta->meta_value, true);
        if (empty($schedule)) {
            $this->workScheduleHint = 'El formato del horario laboral es incorrecto.';
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
            $this->workScheduleHint = 'No hay tramos para hoy.';
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
                $this->workScheduleHint = "Tramo actual sugerido: {$relevantSlot['start']} - {$relevantSlot['end']}";
            } else {
                $this->workScheduleHint = "Último tramo finalizado: {$relevantSlot['start']} - {$relevantSlot['end']}";
            }

            // This part will only execute if the component has an 'event' property
            if (property_exists($this, 'event') && $this->event && $this->event->is_open == 1) {
                $this->event->end = date('Y-m-d') . ' ' . $relevantSlot['end'];
            }
        } else {
            $this->workScheduleHint = 'No se encontró un tramo horario aplicable.';
        }
    }
}
