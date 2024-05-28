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

    public function updatedBrowsedUser()
    {
    }

    public function getData()
    {
        // Get microtime for benchmarking
        $start = microtime(true);

        $events = Event::EventsPerUserMonth($this->browsedUser, $this->selectedMonth, $this->selectedYear, $this->description);
        $this->totalHours = round($events->sum('hours'), 2);

        // As recommended in livewireCharts documentation
        // $cCModel = $events->groupBy('day')
        //     ->reduce(
        //         function ($cCModel, $data) {
        //             $day = $data->first()->day . '/' . $data->first()->month;
        //             $hours = number_format($data->sum('hours'), 2);
        //             return $cCModel->addColumn($day, $hours, '#006600');                    
        //         }, LivewireCharts::columnChartModel() // This the default initial value the first reduce item
        //             ->setTitle( __("Hours worked"))
        //             ->setAnimated($this->firstRun)
        //             ->setLegendVisibility(false)
        //             ->setColumnWidth(90)
        //             ->withGrid()
        //             ->withDataLabels()
        //             //->setDataLabelsEnabled($this->showDataLabels)
        //             //->setHorizontal(true)
        //             // Hint -> After this line, reduce() inserts de $columns array to set columns for graph
        //     );


        // My implementation
        $cCModel = LivewireCharts::columnChartModel() // This the default initial value the first reduce item
            ->setTitle(__("Registered hours"))
            ->setAnimated($this->firstRun)
            ->setLegendVisibility(false)
            ->setColumnWidth(90)
            ->withGrid()
            ->withDataLabels();

        $cols = collect($events->groupBy('day')
            ->reduce(
                function ($cols, $data) {
                    $day = $data->first()->day . '/' . $data->first()->month;
                    $hours = number_format($data->sum('hours'), 2);
                    array_push($cols, array('day' => $day, 'hour' => $hours, 'color' => '#006600'));
                    return $cols;
                },
                [''] //To avoid null in first element of reduce collection
            ));
        $cols->shift(1); //To remove first element of collection
        foreach ($cols as $c) {
            $cCModel->addColumn($c['day'], $c['hour'], $c['color']);
        }

        $elapsedTime = 0;
        $elapsedTime = number_format((microtime(true) - $start) * 1000, 2);
        return [$cCModel, $elapsedTime];
    }

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
