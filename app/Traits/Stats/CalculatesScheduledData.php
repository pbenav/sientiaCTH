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

        // Usar números ISO (1-7) en lugar de letras (L,M,X,J,V,S,D)
        // 1 = Lunes, 2 = Martes, 3 = Miércoles, 4 = Jueves, 5 = Viernes, 6 = Sábado, 7 = Domingo
        $minutesPerDay = array_fill_keys([1, 2, 3, 4, 5, 6, 7], 0);
        
        foreach ($schedule as $slot) {
            if (empty($slot['days']) || empty($slot['start']) || empty($slot['end'])) {
                continue;
            }
            $start = Carbon::parse($slot['start']);
            $end = Carbon::parse($slot['end']);
            $duration = $end->diffInMinutes($start);
            
            foreach ($slot['days'] as $dayNumber) {
                // Asegurar que es un número válido (1-7)
                $dayNum = (int)$dayNumber;
                if ($dayNum >= 1 && $dayNum <= 7 && array_key_exists($dayNum, $minutesPerDay)) {
                    $minutesPerDay[$dayNum] += $duration;
                }
            }
        }

        $totalMinutes = 0;
        $workDays = 0;

        // Use the team's timezone (if available) so "today" matches the team's local day
        $teamTimezone = $this->actualUser->currentTeam->timezone ?? config('app.timezone');
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1, 0, 0, 0, $teamTimezone);
        // If the request is for the current month (in team timezone), limit to today; if past month, use whole month
        $today = Carbon::today($teamTimezone);
        if ($this->selectedYear === (int) $today->year && $this->selectedMonth === (int) $today->month) {
            // keep endDate at the end of 'today' in team timezone
            $endDate = $today->copy()->endOfDay();
        } else {
            $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        }

        $holidays = $this->actualUser->currentTeam->holidays()
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'));

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($holidays->contains($date->format('Y-m-d'))) {
                continue;
            }

            // Obtener número ISO del día (1 = Lunes, 7 = Domingo)
            $dayNumber = (int) $date->format('N');
            $minutesForDay = $minutesPerDay[$dayNumber] ?? 0;

            if ($minutesForDay > 0) {
                $totalMinutes += $minutesForDay;
                $workDays++;
            }
        }

        return [round($totalMinutes / 60, 2), $workDays];
    }

    /**
     * Get the ISO day number for a day of the week.
     * This method is kept for backward compatibility but now directly returns the input
     * since we already use ISO numbers (1-7).
     *
     * @param int $dayOfWeek ISO day number (1=Monday, 7=Sunday)
     * @return int
     */
    private function getDayInitial(int $dayOfWeek): int
    {
        // Ya no necesitamos conversión - devolvemos el número ISO directamente
        return $dayOfWeek;
    }
}
