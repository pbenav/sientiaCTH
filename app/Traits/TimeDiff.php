<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterface;

trait TimeDiff
{

    protected $options = [
        'join' => ', ',
        'parts' => 2,
        'syntax' => CarbonInterface::DIFF_ABSOLUTE,
    ];

    public function timeDiff($start, $end, $forHuman)
    {
        //Set the start date
        $start_date = Carbon::parse($start);
        $end_date = Carbon::parse($end);
        //Set the end date
        if ($start_date < $end_date && $end != null) {
            //Count the difference in Hours and minutes
            if($forHuman){
                return $end_date->diffForHumans($start_date, $this->options);
            } else {
                return $end_date->diffInMinutes($start_date, true);
            }
        } else {
            return __('Incomplete Event');
        }
    }
}
