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

        // Base query for events
        $query = Event::query()
            ->where('user_id', $this->browsedUser)
            ->whereMonth('start', $this->selectedMonth)
            ->whereYear('start', $this->selectedYear)
            ->selectRaw('DAY(start) as day, MONTH(start) as month, SUM(TIMESTAMPDIFF(minute, start, end))/60 as hours')
            ->groupBy('day', 'month')
            ->orderBy('day');

        // Conditionally filter by event type
        $query->when($this->eventTypeId, function ($q) {
            return $q->where('event_type_id', $this->eventTypeId);
        });

        $dailyTotals = $query->get();

        $this->totalHours = round($dailyTotals->sum('hours'), 2);

        // Initialize the chart model
        $columnChart = LivewireCharts::columnChartModel()
            ->setTitle(__("Registered hours"))
            ->setAnimated($this->firstRun)
            ->setLegendVisibility(false)
            ->setColumnWidth(90)
            ->withGrid()
            ->withDataLabels();

        // Add columns to the chart model for each day
        foreach ($dailyTotals as $dayData) {
            $columnChart->addColumn($dayData->day . '/' . $dayData->month, round($dayData->hours, 2), '#006600');
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
