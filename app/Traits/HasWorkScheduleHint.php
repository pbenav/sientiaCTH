<?php

namespace App\Traits;

use App\Models\UserMeta;
use Illuminate\Support\Facades\Auth;

trait HasWorkScheduleHint
{
    public function getWorkScheduleHint()
    {
        if (!Auth::check()) {
            $this->workScheduleHint = '';
            return;
        }

        $user = Auth::user();
        $workSchedule = UserMeta::where('user_id', $user->id)
                                ->where('meta_key', 'work_schedule')
                                ->first();

        if ($workSchedule) {
            $schedule = json_decode($workSchedule->meta_value, true);
            $now = new \DateTime();
            $currentTime = $now->format('H:i:s');
            $currentDay = $now->format('N'); // 1 (for Monday) through 7 (for Sunday)
            $dayMap = ['L' => 1, 'M' => 2, 'X' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7];

            foreach ($schedule as $slot) {
                $days = $slot['days'] ?? [];
                $appliesToToday = false;
                foreach($days as $day) {
                    if (isset($dayMap[$day]) && $dayMap[$day] == $currentDay) {
                        $appliesToToday = true;
                        break;
                    }
                }

                if ($appliesToToday && $currentTime >= $slot['start'] && $currentTime <= $slot['end']) {
                    $this->workScheduleHint = "Tramo actual: {$slot['start']} - {$slot['end']}";
                    return;
                }
            }
        }
        $this->workScheduleHint = 'No se encuentra en ningún tramo horario definido.';
    }
}
