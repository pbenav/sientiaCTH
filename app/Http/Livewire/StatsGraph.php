<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;

class StatsGraph extends Component
{
    public $totalHours;
    public $selectedMonth = 10;

    public $firstRun = true;

    public $showDataLabels = false;

    public function render()
    {
        $data = Event::query()->EventsPerUser(Auth::user()->id, $this->selectedMonth);

        $this->totalHours = array_sum($data->values()->toArray());

        //dd($data->keys()->toArray());

        $cCM =
            (new ColumnChartModel())
                ->setTitle(
                    'Expenses by Type'
                )
                ->addColumn(
                    'Food',
                    100,
                    '#f6ad55'
                )
                ->addColumn(
                    'Shopping',
                    200,
                    '#fc8181'
                )
                ->addColumn(
                    'Travel',
                    300,
                    '#90cdf4'
                )
            ;

        $this->firstRun = false;

        return view('livewire.stats-graph')
            ->with(
                [
                    'cCM' => $cCM,
                ]
            );
    }
}