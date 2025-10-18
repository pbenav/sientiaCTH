<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Carbon\Carbon;
use Livewire\Attributes\On;

class StatsComponent extends Component
{
    #[On('onColumnClick')]
    public function onColumnClick($column)
    {
        $dayAndMonth = $column['title'];
        $date = Carbon::createFromFormat('d/m Y', $dayAndMonth . ' ' . $this->selectedYear);

        $events = Event::query()
            ->with('eventType')
            ->where('user_id', $this->browsedUser)
            ->whereDate('start', $date)
            ->orderBy('start', 'asc')
            ->get();

        $this->dispatch('open-events-modal', events: $events->toArray());
    }
    public $totalHours;
    public $selectedMonth;
    public $selectedYear;
    public $eventTypeId;
    public $eventTypes;
    public $firstRun = true;
    public $showDataLabels = true;
    public $hasData = true;
    public User $actualUser;
    public $browsedUser;
    public $isTeamAdmin;
    public $isInspector;
    public $workers = [];
    public $paso;
    public $totalDays = 0;
    public $dashboardData = [];

    public function mount()
    {
        $this->selectedMonth = date('m');
        $this->selectedYear = date('Y');
        $this->actualUser = User::find(Auth::user()->id);
        $this->browsedUser = $this->actualUser->id;
        $this->isTeamAdmin = $this->actualUser->isTeamAdmin();
        $this->isInspector = $this->actualUser->isInspector();
        if ($this->isTeamAdmin || $this->isInspector) {
            $this->workers = $this->actualUser->currentTeam->allUsers();
        }
        $this->eventTypes = $this->actualUser->currentTeam->eventTypes ?? collect();
        $this->eventTypeId = null;
    }

    protected $rules = [
        'browsedUser' => 'required|exists:users',
    ];

    public function updatedBrowsedUser()
    {
    }

    public function getData()
    {
        $start = microtime(true);
        $this->hasData = true;

        $query = Event::with('eventType')
            ->where('user_id', $this->browsedUser)
            ->whereYear('start', $this->selectedYear)
            ->where(function ($q) {
                $q->whereMonth('start', $this->selectedMonth)
                  ->orWhereMonth('end', $this->selectedMonth);
            });

        if ($this->eventTypeId) {
            $query->where('event_type_id', $this->eventTypeId);
        }

        $events = $query->get();

        $processedEvents = [];
        $daysWithEvents = [];
        $eventTypesInUse = [];

        foreach ($events as $event) {
            if (!$event->end || !$event->eventType) continue;

            $eventTypesInUse[$event->eventType->name] = $event->eventType;

            $start_date = Carbon::parse($event->start);
            $end_date = Carbon::parse($event->end);

            for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
                if ($date->month != $this->selectedMonth) continue;

                $dayKey = $date->format('d/m');
                $daysWithEvents[$dayKey] = $date;

                $day_start = $date->copy()->startOfDay();
                $day_end = $date->copy()->endOfDay();
                $effective_start = $start_date->max($day_start);
                $effective_end = $end_date->min($day_end);

                if ($effective_start->lt($effective_end)) {
                    $hours_for_day = $effective_start->diffInSeconds($effective_end) / 3600;
                    $processedEvents[$dayKey][$event->eventType->name] = ($processedEvents[$dayKey][$event->eventType->name] ?? 0) + $hours_for_day;
                }
            }
        }

        uasort($daysWithEvents, function ($a, $b) {
            return $a <=> $b;
        });
        $xAxisData = array_keys($daysWithEvents);

        $dailyTypeHours = [];
        foreach ($daysWithEvents as $dayKey => $dateObject) {
            foreach ($eventTypesInUse as $typeName => $eventType) {
                $hours = $processedEvents[$dayKey][$typeName] ?? null;
                $dailyTypeHours[$dayKey][$typeName] = ['hours' => $hours, 'color' => $eventType->color];
            }
        }

        $totalHours = 0;
        $dayCountsPerType = [];
        $uniqueDays = [];

        $workdayEventType = $this->actualUser->currentTeam->eventTypes()->where('is_workday_type', true)->first();

        foreach ($dailyTypeHours as $day => $types) {
            if ($workdayEventType) {
                if (isset($types[$workdayEventType->name])) {
                    $totalHours += $types[$workdayEventType->name]['hours'];
                }
            }
            $uniqueDays[$day] = true;

            foreach ($types as $typeName => $data) {
                if (!isset($dayCountsPerType[$typeName])) {
                    $dayCountsPerType[$typeName] = [];
                }
                $dayCountsPerType[$typeName][$day] = true;
            }
        }

        $this->totalHours = round($totalHours, 2);

        if ($this->eventTypeId) {
            $this->totalDays = count($dailyTypeHours);
        } else {
            $maxDays = 0;
            foreach ($dayCountsPerType as $typeName => $days) {
                if (count($days) > $maxDays) {
                    $maxDays = count($days);
                }
            }
            $this->totalDays = $maxDays;
        }

        if (empty($dailyTypeHours)) {
            $this->hasData = false;
            return [LivewireCharts::multiColumnChartModel(), 0];
        }

        if ($this->eventTypeId && !empty($dailyTypeHours)) {
            $columnChart = LivewireCharts::columnChartModel()
                ->setTitle(__("Registered hours"))
                ->setAnimated($this->firstRun)
                ->withDataLabels()
                ->withOnColumnClickEventName('onColumnClick');

            foreach ($dailyTypeHours as $day => $types) {
                $typeData = array_values($types)[0];
                $hours = $typeData['hours'];
                $color = $typeData['color'];
                $columnChart->addColumn($day, $hours !== null ? round($hours, 2) : null, $color);
            }
        } else {
            $columnChart = LivewireCharts::multiColumnChartModel()
                ->setTitle(__("Registered hours"))
                ->setAnimated($this->firstRun)
                ->withDataLabels()
                ->withOnColumnClickEventName('onColumnClick')
                ->setXAxisCategories($xAxisData);

            foreach ($dailyTypeHours as $day => $types) {
                foreach ($types as $typeName => $data) {
                    $columnChart->addSeriesColumn($typeName, $day, $data['hours'] !== null ? round($data['hours'], 2) : null, $data['color']);
                }
            }
        }

        $this->firstRun = false;
        $elapsedTime = number_format((microtime(true) - $start) * 1000, 2);

        return [$columnChart, $elapsedTime];
    }

    public function render()
    {
        list($columnChartModel, $elapsedTime) = $this->getData();
        list($scheduledHours, $scheduledDays) = $this->getScheduledData();
        $this->dashboardData = $this->getDashboardData($scheduledHours, $scheduledDays);

        return view('livewire.stats.stats')
            ->with([
                'columnChartModel' => $columnChartModel,
                'elapsedTime' => $elapsedTime,
                'scheduledHours' => $scheduledHours,
                'scheduledDays' => $scheduledDays,
                'dashboardData' => $this->dashboardData,
            ]);
    }

    private function getDashboardData($scheduledHours, $scheduledDays)
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

        return [
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

    private function getScheduledData()
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

    private function getDayInitial($dayOfWeek)
    {
        $days = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
        return $days[$dayOfWeek - 1];
    }
}
