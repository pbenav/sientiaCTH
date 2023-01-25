<?php

namespace App\Http\Livewire;

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
    public $actualUser;
    public $browsedUser;
    public $isTeamAdmin;
    public $isInspector;
    public $workers = [];

    public function mount(){
        $this->selectedMonth = date('m');
        $this->selectedYear = date('Y');
        $this->actualUser = Auth::user();
        $this->browsedUser = $this->actualUser->id;
        $team = $this->actualUser->currentTeam;
        $this->isTeamAdmin = Auth::user()->isTeamAdmin();
        $this->isInspector = Auth::user()->isInspector();
        if ($this->isTeamAdmin || $this->isInspector) {
            $this->workers = $team->allUsers();
        }
    }
    protected $rules = [
        'browsedUser' => 'required|exists:users',
    ];

    public function updatedBrowsedUser(){
    }

    public function render()
    {
        $events = Event::EventsPerUserMonth($this->browsedUser, $this->selectedMonth, $this->selectedYear, $this->description);
        $this->totalHours = round($events->sum('hours'), 2);

        $columnChartModel = $events->groupBy('day')
            ->reduce(
                function ($columnChartModel, $data) {
                    $day = $data->first()->day . '/' . $data->first()->month;
                    $hours = number_format($data->sum('hours'), 2);
                    return $columnChartModel->addColumn($day, $hours, '#006600');
                }, LivewireCharts::columnChartModel()
                    ->setTitle( __("Hours worked"))
                    ->setAnimated($this->firstRun)
                    ->setLegendVisibility(false)
                    //->setDataLabelsEnabled($this->showDataLabels)
                    ->setColumnWidth(90)
                    //->setHorizontal(true)
                    ->withGrid()
            );

        return view('livewire.stats')
            ->with([
                'columnChartModel' => $columnChartModel,
            ]);
    }
}