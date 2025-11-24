<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EventsPdfExport implements FromView
{
    protected $events;

    public function __construct($events)
    {
        $this->events = $events;
    }

    public function view(): View
    {
        return view('exports.events', [
            'events' => $this->events
        ]);
    }
}
