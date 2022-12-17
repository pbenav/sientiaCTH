<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;

class StatsGraph extends Component
{
    public $totalHours;
    public $selectedMonth = 11;
    public $description;
    public $firstRun = true;
    public $showDataLabels = true;

    public function render()
    {
        $events = Event::EventsPerUserMonth(Auth::user()->id, $this->selectedMonth, $this->description);        
        $this->totalHours = $events->sum('hours');

        $columnChartModel = $events->groupBy('day')
            ->reduce(
                function ($columnChartModel, $data) {
                    $day = $data->first()->day;
                    $hours = $data->first()->hours;
                return $columnChartModel->addColumn($day, $hours, '#f66665');
                }, LivewireCharts::columnChartModel()
                    ->setTitle('Horas trabajadas')
                    ->setAnimated($this->firstRun)
                    ->setLegendVisibility(false)
                    ->setDataLabelsEnabled($this->showDataLabels)
                    ->setColumnWidth(90)
                    ->withGrid()
            );


        return view('livewire.stats-graph')
        ->with([
                'columnChartModel' => $columnChartModel,
            ]);
    }
}