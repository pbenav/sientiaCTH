<?php

namespace App\Exceptions;

use Exception;
use App\Models\Event;

class MaxWorkdayDurationExceededException extends Exception
{
    public $maxMinutes;
    public $currentMinutes;
    public $event;

    public function __construct(int $maxMinutes, int $currentMinutes, Event $event)
    {
        $this->maxMinutes = $maxMinutes;
        $this->currentMinutes = $currentMinutes;
        $this->event = $event;

        parent::__construct(__('Maximum workday duration exceeded (:minutes min). Current: :current min.', [
            'minutes' => $maxMinutes,
            'current' => $currentMinutes
        ]));
    }
}
