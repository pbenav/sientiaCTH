<?php

namespace App\Traits\Stats;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;

trait CalculatesDashboardData
{
    /**
     * Get the data for the dashboard.
     *
     * @param float $scheduledHours
     * @param int $scheduledDays
     * @return array
     */
    private function getDashboardData(float $scheduledHours, int $scheduledDays): array
    {
        $user = User::find($this->browsedUser);
        $workdayEventType = $user->currentTeam->eventTypes()->where('is_workday_type', true)->first();
        if (!$workdayEventType) {
            return [];
        }

        $allEvents = Event::query()
            ->where('user_id', $this->browsedUser)
            ->whereMonth('start', $this->selectedMonth)
            ->whereYear('start', $this->selectedYear)
            ->orderBy('start', 'asc')
            ->get();

        $nonWorkdayHours = $allEvents->where('event_type_id', '!=', $workdayEventType->id)->sum(function ($event) {
            return Carbon::parse($event->start)->diffInHours(Carbon::parse($event->end));
        });

        $events = $allEvents->where('event_type_id', $workdayEventType->id);

        $registeredHours = $events->sum(function ($event) {
            return Carbon::parse($event->start)->diffInHours(Carbon::parse($event->end));
        });

        $effectiveScheduledHours = max(0, $scheduledHours - $nonWorkdayHours);
        $percentage_completion = ($effectiveScheduledHours > 0) ? round(($registeredHours / $effectiveScheduledHours) * 100, 2) : 0;
        $extra_hours = ($registeredHours > $effectiveScheduledHours) ? $registeredHours - $effectiveScheduledHours : 0;

        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];
        $punctualDays = 0;
        $absentDays = 0;
        $workedDays = $events->groupBy(function ($event) {
            return Carbon::parse($event->start)->format('Y-m-d');
        })->keys();

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
            $daySchedule = collect($schedule)->first(function ($slot) use ($dayInitial) {
                return in_array($dayInitial, $slot['days']);
            });

            if ($daySchedule) {
                $isWorked = $workedDays->contains($date->format('Y-m-d'));

                if (!$isWorked) {
                    $absentDays++;
                } else {
                    $firstEvent = $events->first(function ($event) use ($date) {
                        return Carbon::parse($event->start)->isSameDay($date);
                    });

                    if ($firstEvent) {
                        $scheduledStartTime = Carbon::parse($date->format('Y-m-d') . ' ' . $daySchedule['start']);
                        $actualStartTime = Carbon::parse($firstEvent->start);
                        if ($actualStartTime <= $scheduledStartTime) {
                            $punctualDays++;
                        }
                    }
                }
            }
        }

        $workedDaysCount = $scheduledDays - $absentDays;
        $punctuality = ($workedDaysCount > 0) ? round(($punctualDays / $workedDaysCount) * 100, 2) : 0;

        $confidenceScores = [];
        foreach ($events as $event) {
            if (!$event->end) continue;

            $start = Carbon::parse($event->start);
            $end = Carbon::parse($event->end);
            $createdAt = Carbon::parse($event->created_at);
            $updatedAt = Carbon::parse($event->updated_at);

            $diffStart = abs($start->diffInSeconds($createdAt));
            $diffEnd = abs($end->diffInSeconds($updatedAt));
            $duration = abs($start->diffInSeconds($end));

            if ($duration > 0) {
                $totalDiff = $diffStart + $diffEnd;
                $confidence = max(0, (1 - ($totalDiff / $duration)) * 100);
                $confidenceScores[] = $confidence;
            }
        }

        $avgConfidence = !empty($confidenceScores) ? round(array_sum($confidenceScores) / count($confidenceScores), 2) : 0;
        $minConfidence = !empty($confidenceScores) ? round(min($confidenceScores), 2) : 0;
        $maxConfidence = !empty($confidenceScores) ? round(max($confidenceScores), 2) : 0;

        $exceptionalEventsCount = Event::where('user_id', $this->browsedUser)
            ->where('is_exceptional', true)
            ->whereYear('start', $this->selectedYear)
            ->whereMonth('start', $this->selectedMonth)
            ->count();

        $automaticallyClosedCount = Event::where('user_id', $this->browsedUser)
            ->where('is_closed_automatically', true)
            ->whereYear('updated_at', $this->selectedYear)
            ->whereMonth('updated_at', $this->selectedMonth)
            ->count();

        return [
            'exceptional_events_count' => $exceptionalEventsCount,
            'automatically_closed_count' => $automaticallyClosedCount,
            'percentage_completion' => $percentage_completion,
            'extra_hours' => $extra_hours,
            'punctuality' => $punctuality,
            'absenteeism' => $absentDays,
            'registered_hours' => $registeredHours,
            'effective_scheduled_hours' => $effectiveScheduledHours,
            'avg_confidence' => $avgConfidence,
            'min_confidence' => $minConfidence,
            'max_confidence' => $maxConfidence,
        ];
    }
}
