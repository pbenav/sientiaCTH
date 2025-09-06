<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;

class StatsComponent extends Component
{
    public $totalHours;
    public $selectedMonth;
    public $selectedYear;
    public $eventTypeId;
    public $eventTypes;
    public $firstRun = true;
    public $showDataLabels = true;
    public User $actualUser;
    public $browsedUser;
    public $isTeamAdmin;
    public $isInspector;
    public $workers = [];
    public $paso;

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

        // 1. Fetch raw events for the month, filtered by user
        $query = Event::query()
            ->with('eventType') // Eager load for color and name
            ->where('user_id', $this->browsedUser)
            ->where(function ($q) {
                $q->whereMonth('start', $this->selectedMonth)
                  ->orWhereMonth('end', $this->selectedMonth);
            })
            ->whereYear('start', $this->selectedYear);

        // Conditionally filter by event type
        $query->when($this->eventTypeId, function ($q) {
            return $q->where('event_type_id', $this->eventTypeId);
        });

        $events = $query->get();

        // 2. Process events in PHP to handle splitting across days
        $dailyTypeHours = [];
        $totalHours = 0;

        foreach ($events as $event) {
            if (!$event->end || !$event->eventType) {
                continue;
            }

            $start_date = new \DateTime($event->start);
            $end_date = new \DateTime($event->end);
            $current_date = clone $start_date;

            while ($current_date->format('Y-m-d') <= $end_date->format('Y-m-d')) {
                // Only process days within the selected month
                if ($current_date->format('m') != $this->selectedMonth) {
                    $current_date->modify('+1 day');
                    continue;
                }

                $day_start = (clone $current_date)->setTime(0, 0, 0);
                $day_end = (clone $current_date)->setTime(23, 59, 59);

                $effective_start = max($start_date, $day_start);
                $effective_end = min($end_date, $day_end);

                if ($effective_start < $effective_end) {
                    $diff_in_seconds = $effective_end->getTimestamp() - $effective_start->getTimestamp();
                    $hours_for_day = $diff_in_seconds / 3600;
                    $totalHours += $hours_for_day;

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

        $this->totalHours = round($totalHours, 2);

        // 3. Build the multi-series column chart
        $columnChart = LivewireCharts::multiColumnChartModel()
            ->setTitle(__("Registered hours"))
            ->setAnimated($this->firstRun)
            ->withDataLabels();

        ksort($dailyTypeHours); // Sort by day

        foreach ($dailyTypeHours as $day => $types) {
            foreach ($types as $typeName => $data) {
                $columnChart->addSeriesColumn($typeName, $day, round($data['hours'], 2), $data['color']);
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
        list($cCModel, $elapsedTime) = $this->getData();

        return view('livewire.stats.stats')
            ->with([
                'columnChartModel' => $cCModel,
                'elapsedTime' => $elapsedTime,
            ]);
    }
}
