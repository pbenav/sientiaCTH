<?php

namespace App\Traits\Stats;

use App\Models\User;
use Carbon\Carbon;

trait CalculatesScheduledData
{
    /**
     * Get the scheduled data for the user.
     *
     * @return array
     */
    private function getScheduledData(): array
    {
        $user = User::find($this->browsedUser);
        if (!$user || !$user->meta) {
            return [0, 0];
        }

        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();

        if (!$scheduleMeta || !$scheduleMeta->meta_value) {
            return [0, 0];
        }

        $schedule = json_decode($scheduleMeta->meta_value, true);
        if (empty($schedule)) {
            return [0, 0];
        }

        $minutesPerDay = array_fill_keys(['L', 'M', 'X', 'J', 'V', 'S', 'D'], 0);
        foreach ($schedule as $slot) {
            if (empty($slot['days']) || empty($slot['start']) || empty($slot['end'])) {
                continue;
            }
            $start = Carbon::parse($slot['start']);
            $end = Carbon::parse($slot['end']);
            $duration = $end->diffInMinutes($start);
            foreach ($slot['days'] as $dayInitial) {
                if (array_key_exists($dayInitial, $minutesPerDay)) {
                    $minutesPerDay[$dayInitial] += $duration;
                }
            }
        }

        $totalMinutes = 0;
        $workDays = 0;

        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $holidays = $this->actualUser->currentTeam->holidays()
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'));

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($holidays->contains($date->format('Y-m-d'))) {
                continue;
            }

            $dayInitial = $this->getDayInitial($date->format('N'));
            $minutesForDay = $minutesPerDay[$dayInitial];

            if ($minutesForDay > 0) {
                $totalMinutes += $minutesForDay;
                $workDays++;
            }
        }

        return [round($totalMinutes / 60, 2), $workDays];
    }

    /**
     * Get the initial for a day of the week.
     *
     * @param int $dayOfWeek
     * @return string
     */
    private function getDayInitial(int $dayOfWeek): string
    {
        $days = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
        return $days[$dayOfWeek - 1];
    }
}
