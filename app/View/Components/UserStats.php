<?php

namespace App\View\Components;

use Illuminate\View\Component;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class UserStats extends Component
{
    public $chart_options;
    public $chart1;

    public function mount(){
  
    $this->chart_options = [
        'chart_title' => 'Users by months',
        'report_type' => 'group_by_date',
        'model' => 'App\Models\User',
        'group_by_field' => 'created_at',
        'group_by_period' => 'month',
        'chart_type' => 'bar',
    ];

    $this->chart1 = new LaravelChart($this->chart_options);
}

public function render(){
    return view('components.user_stats', compact('chart1'));
}

}