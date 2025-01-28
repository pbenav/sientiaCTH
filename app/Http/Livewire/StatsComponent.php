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
    public $description;
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
        // Get microtime for benchmarking
        $start = microtime(true);

        // Fetch events data for the selected user, month, year, and description
        $events = Event::EventsPerUserMonth($this->browsedUser, $this->selectedMonth, $this->selectedYear, $this->description);
        $this->totalHours = round($events->sum('hours'), 2);

        // Initialize the chart model
        $cCModel = LivewireCharts::columnChartModel() // Default initial value for the chart
            ->setTitle(__("Registered hours"))
            ->setAnimated($this->firstRun)
            ->setLegendVisibility(false)
            ->setColumnWidth(90)
            ->withGrid()
            ->withDataLabels();

        // Group events by day and add columns to the chart model
        $cols = collect($events->groupBy('day')
            ->reduce(
                function ($cols, $data) {
                    $day = $data->first()->day . '/' . $data->first()->month;
                    $hours = number_format($data->sum('hours'), 2);
                    array_push($cols, array('day' => $day, 'hour' => $hours, 'color' => '#006600'));
                    return $cols;
                },
                [''] // To avoid null in the first element of the reduce collection
            ));
        $cols->shift(1); // Remove the first element of the collection

        // Add columns to the chart model
        foreach ($cols as $c) {
            $cCModel->addColumn($c['day'], $c['hour'], $c['color']);
        }

        // Calculate elapsed time for benchmarking
        $elapsedTime = number_format((microtime(true) - $start) * 1000, 2);

        return [$cCModel, $elapsedTime];
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
