<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use App\Models\Event;
use Livewire\Component;
use App\Charts\TimeRegistersChart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// This class is for using stats as a Livweire Component

class StatsGraph extends Component
{

    public $selectedMonth = 11;
    public $totalHours;    
    
    public function render()
    {
        $availableMonths = [
            date('m'), date('m') - 1, date('m') - 2, date('m') - 3,
        ];
        
        $data = Event::query()->EventsPerUser(Auth::user()->id, $this->selectedMonth);

        $this->totalHours = array_sum($data->values()->toArray());
        
        $chart = new TimeRegistersChart;
        $chart->labels($data->keys());
        $chart->dataset(__('Worked hours'), 'bar', $data->values())->backgroundColor('red');
        return view('livewire.stats-graph', compact('chart', 'availableMonths'));
    }
}
