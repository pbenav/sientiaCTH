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

    /**
     * Mounts the component and initializes necessary data.
     *
     * This method is called once when the component is initialized.
     * It sets up user, team, permissions, and the current selected month and year.
     */
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

    /**
     * Handles updates to the browsedUser property.
     *
     * This method is triggered when the browsedUser property is updated.
     * It does not perform any action in this case, but can be extended for future use.
     */
    public function updatedBrowsedUser()
    {
    }

    /**
     * Retrieves the data for the selected user, month, year, and description.
     *
     * This method fetches the events data for the selected user and time period,
     * calculates the total hours, and prepares the chart model.
     *
     * @return array An array containing the chart model and elapsed time.
     */
    public function getData()
    {
        $start = microtime(true);
        $this->hasData = true;

        // 1. Fetch raw events
        $query = Event::query()
            ->with('eventType')
            ->join('users', 'events.user_id', '=', 'users.id')
            ->leftJoin('event_types', 'events.event_type_id', '=', 'event_types.id')
            ->where(function ($query) {
                $query->where('event_types.team_id', $this->actualUser->currentTeam->id)
                      ->orWhereNull('events.event_type_id');
            })
            ->where('user_id', $this->browsedUser)
            ->where(function ($q) {
                $q->whereMonth('start', $this->selectedMonth)
                  ->orWhereMonth('end', $this->selectedMonth);
            })
            ->whereYear('start', $this->selectedYear);
        $query->when($this->eventTypeId, fn($q) => $q->where('event_type_id', $this->eventTypeId));
        $events = $query->get();

        // 2. Process events into a data structure grouped by type and day
        $dailyTypeHours = [];
        foreach ($events as $event) {
            if (!$event->end || !$event->eventType) continue;

            $start_date = new \DateTime($event->start);
            $end_date = new \DateTime($event->end);
            $current_date = clone $start_date;

            while ($current_date->format('Y-m-d') <= $end_date->format('Y-m-d')) {
                if ($current_date->format('m') != $this->selectedMonth) {
                    $current_date->modify('+1 day');
                    continue;
                }
                $day_start = (clone $current_date)->setTime(0, 0, 0);
                $day_end = (clone $current_date)->setTime(23, 59, 59);
                $effective_start = max($start_date, $day_start);
                $effective_end = min($end_date, $day_end);

                if ($effective_start < $effective_end) {
                    $hours_for_day = ($effective_end->getTimestamp() - $effective_start->getTimestamp()) / 3600;
                    $dayKey = $current_date->format('d/m');
                    $typeKey = $event->eventType->name;
                    $color = $event->eventType->color;
                    if (!isset($dailyTypeHours[$dayKey])) {
                        $dailyTypeHours[$dayKey] = [];
                    }
                    if (!isset($dailyTypeHours[$dayKey][$typeKey])) {
                        $dailyTypeHours[$dayKey][$typeKey] = ['hours' => 0, 'color' => $color];
                    }
                    $dailyTypeHours[$dayKey][$typeKey]['hours'] += $hours_for_day;
                }
                $current_date->modify('+1 day');
            }
        }

        // Calculate total from the processed data
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

        // 3. Handle No Data
        if (empty($dailyTypeHours)) {
            $this->hasData = false;
            return [LivewireCharts::multiColumnChartModel(), 0];
        }

        // 4. Build the appropriate chart based on filters
        if ($this->eventTypeId && !empty($dailyTypeHours)) {
            // SINGLE-SERIES CHART for a filtered event type
            $columnChart = LivewireCharts::columnChartModel()
                ->setTitle(__("Registered hours"))
                ->setAnimated($this->firstRun)
                ->withDataLabels()
                ->withOnColumnClickEventName('onColumnClick');

            foreach ($dailyTypeHours as $day => $types) {
                $typeData = array_values($types)[0];
                $hours = $typeData['hours'];
                $color = $typeData['color'];
                $columnChart->addColumn($day, round($hours, 2), $color);
            }
        } else {
            // MULTI-SERIES CHART for all event types
            $columnChart = LivewireCharts::multiColumnChartModel()
                ->setTitle(__("Registered hours"))
                ->setAnimated($this->firstRun)
                ->withDataLabels()
                ->withOnColumnClickEventName('onColumnClick');

            ksort($dailyTypeHours);

            foreach ($dailyTypeHours as $day => $types) {
                foreach ($types as $typeName => $data) {
                    $columnChart->addSeriesColumn($typeName, $day, round($data['hours'], 2), $data['color']);
                }
            }
        }

        $this->firstRun = false;
        $elapsedTime = number_format((microtime(true) - $start) * 1000, 2);

        return [$columnChart, $elapsedTime];
    }

    /**
     * Renders the component view.
     *
     * This method is responsible for rendering the Livewire component's view and passing the necessary data to it.
     *
     * @return \Illuminate\View\View The rendered view.
     */
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
        // 1. Fetch user and events
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

        // 2. Calculate Metrics
        $effectiveScheduledHours = max(0, $scheduledHours - $nonWorkdayHours);
        $percentage_completion = ($effectiveScheduledHours > 0) ? round(($registeredHours / $effectiveScheduledHours) * 100, 2) : 0;
        $extra_hours = ($registeredHours > $effectiveScheduledHours) ? $registeredHours - $effectiveScheduledHours : 0;

        // Punctuality & Absenteeism
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


        return [
            'percentage_completion' => $percentage_completion,
            'extra_hours' => $extra_hours,
            'punctuality' => $punctuality,
            'absenteeism' => $absentDays,
            'registered_hours' => $registeredHours,
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

        // Pre-calculate total minutes scheduled for each day of the week
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
