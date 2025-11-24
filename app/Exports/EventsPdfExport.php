<?php

namespace App\Exports;

use Spatie\Browsershot\Browsershot;

class EventsPdfExport
{
    protected $events;

    public function __construct($events)
    {
        $this->events = $events;
    }

    public function generate(): string
    {
        $html = view('exports.events_pdf', [
            'events' => $this->events
        ])->render();

        return Browsershot::html($html)
            ->format('A4')
            ->landscape()
            ->margins(10, 10, 10, 10)
            ->pdf();
    }
}
