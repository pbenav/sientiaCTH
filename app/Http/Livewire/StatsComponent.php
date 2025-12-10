<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use App\Traits\Stats\CalculatesDashboardData;
use App\Traits\Stats\CalculatesScheduledData;
use Carbon\Carbon;
use Livewire\Attributes\On;

/**
 * A Livewire component for displaying statistics and charts.
 */
class StatsComponent extends Component
{
    use CalculatesDashboardData;
    use CalculatesScheduledData;

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

    // Inicializaciones por defecto para evitar "accessed before initialization"
    public float $totalHours = 0.0;
    public int $selectedMonth = 1;
    public int $selectedYear = 0;
    public string|int|null $eventTypeId = null;
    public array|object $eventTypes = [];
    public bool $firstRun = true;
    public bool $showDataLabels = true;
    public bool $hasData = true;
    public ?User $actualUser = null;
    public ?int $browsedUser = null;
    public bool $isTeamAdmin = false;
    public bool $isInspector = false;
    public array $workers = [];
    public $paso = null;
    public int $totalDays = 0;
    public array $dashboardData = [];

    public function mount()
    {
        // Safe initialization
        $this->selectedMonth = (int) date('m');
        $this->selectedYear = (int) date('Y');

        $this->actualUser = Auth::user() ? User::find(Auth::user()->id) : null;
        $this->browsedUser = $this->actualUser->id ?? null;
        $this->isTeamAdmin = $this->actualUser?->isTeamAdmin() ?? false;
        $this->isInspector = $this->actualUser?->isInspector() ?? false;

        if ($this->isTeamAdmin || $this->isInspector) {
            // allUsers() can return array or collection; normalize afterwards
            $this->workers = $this->actualUser->currentTeam->allUsers()->toArray();
        }

        // Normalizar eventTypes (collection/array)
        $this->eventTypes = $this->actualUser?->currentTeam?->eventTypes ?? collect();

        // Normalizar workers: convertir arrays a objetos para la vista
        $this->workers = collect($this->workers)->map(function ($w) {
            return is_array($w) ? (object) $w : $w;
        })->all();

        $this->eventTypeId = null;
    }

    protected $rules = [
        'browsedUser' => 'required|exists:users',
    ];

    public function updatedBrowsedUser(): void
    {
        // placeholder: add logic if needed
    }

    public function updatedEventTypeId($value): void
    {
        // Convert empty string to null for proper type handling
        if ($value === '' || $value === 'all') {
            $this->eventTypeId = null;
        }
    }

    public function getData(): array
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

        // Fetch holidays for the selected month
        $teamTimezone = $this->actualUser->currentTeam->timezone ?? config('app.timezone');
        $startDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1, 0, 0, 0, $teamTimezone);
        $endDate = $startDate->copy()->endOfMonth();
        
        $holidays = $this->actualUser->currentTeam->holidays()
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'));

        // Parse work schedule to get working days (1-7)
        $workingDays = [1, 2, 3, 4, 5]; // Default M-F
        $scheduleMeta = $this->actualUser->meta->where('meta_key', 'work_schedule')->first();
        if ($scheduleMeta && $scheduleMeta->meta_value) {
            $schedule = json_decode($scheduleMeta->meta_value, true);
            if (!empty($schedule)) {
                $workingDays = [];
                foreach ($schedule as $slot) {
                    if (!empty($slot['days'])) {
                        foreach ($slot['days'] as $day) {
                            $workingDays[] = (int)$day;
                        }
                    }
                }
                $workingDays = array_unique($workingDays);
            }
        }



// Process each event. Full‑day events (00:00 → 00:00 next day) are counted only on the start day
        foreach ($events as $event) {
            if (!$event->end || !$event->eventType) continue;

            $eventTypesInUse[$event->eventType->name] = $event->eventType;

            // Parse dates as UTC since they are stored in UTC in the database
            $start_date = Carbon::parse($event->start, 'UTC');
            $end_date = Carbon::parse($event->end, 'UTC');

            for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
                if ($date->month != $this->selectedMonth) continue;

                $dayKey = $date->format('d/m');

                // Filter out non-working days for non-workday events (e.g. Vacations)
                // This applies to both all-day and partial events to ensure accurate stats
                if ($event->eventType && !$event->eventType->is_workday_type) {
                     if ($holidays->contains($date->format('Y-m-d'))) {
                         continue;
                     }
                     if (!in_array($date->format('N'), $workingDays)) {
                         continue;
                     }
                }

                // Special handling for all-day events
                if ($event->eventType->is_all_day) {
                     // If the current day starts at or after the end time, skip it.
                     // This handles events ending at 00:00:00 (don't count that day)
                     if ($date->gte($end_date)) {
                         continue;
                     }

                     
                     // Force 24 hours for all-day events (rounding to full day)
                     $hours_for_day = 24;
                     $processedEvents[$dayKey][$event->eventType->name] = ($processedEvents[$dayKey][$event->eventType->name] ?? 0) + $hours_for_day;
                     $daysWithEvents[$dayKey] = $date;
                     continue;
                }
                
                $day_start = $date->copy()->startOfDay();
                $day_end = $date->copy()->endOfDay();
                $effective_start = $start_date->max($day_start);
                $effective_end = $end_date->min($day_end);

                if ($effective_start->lt($effective_end)) {
                    $hours_for_day = $effective_start->diffInSeconds($effective_end) / 3600;
                    $processedEvents[$dayKey][$event->eventType->name] = ($processedEvents[$dayKey][$event->eventType->name] ?? 0) + $hours_for_day;
                    $daysWithEvents[$dayKey] = $date;
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
        // Log KPI values for verification
        Log::info('KPI calculated', ['totalHours' => $this->totalHours, 'totalDays' => $this->totalDays]);

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
}