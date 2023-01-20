<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

trait TimeDiff
{

    protected $options = [
        'join' => ', ',
        'parts' => 2,
        'syntax' => CarbonInterface::DIFF_ABSOLUTE,
    ];

    public function timeDiff($start, $end)
    {
        //Set the start date
        $start_date = Carbon::parse($start);
        $end_date = Carbon::parse($end);
        //Set the end date
        if ($end != null) {
            //Count the difference in Hours and minutes
            return $end_date->diffForHumans($start_date, $this->options);
        } else {
            return $start;
        }
    }
}
