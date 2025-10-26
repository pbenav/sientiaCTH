<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Provides a method for calculating the difference between two times.
 *
 * This trait is used to calculate the duration of an event, either in a
 * human-readable format or in seconds.
 */
trait TimeDiff
{
    /**
     * The options to use when calculating the time difference for humans.
     *
     * @var array
     */
    protected $options = [
        'join' => ', ',
        'parts' => 2,
        'syntax' => CarbonInterface::DIFF_ABSOLUTE,
    ];

    /**
     * Calculate the difference between two times.
     *
     * @param string $start The start time.
     * @param string|null $end The end time.
     * @param boolean $forHuman Whether to return the difference in a human-readable format.
     * @return string|int
     */
    public function timeDiff(string $start, ?string $end, bool $forHuman): string|int
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
                return $end_date->diffInRealSeconds($start_date, true);
            }
        } else {
            return __('On course Event');
        }
    }
}
