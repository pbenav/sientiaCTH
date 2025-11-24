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
        // Force Spanish locale for translations
        app()->setLocale('es');
        
        $html = view('exports.events_pdf', [
            'events' => $this->events
        ])->render();

        $footerText = __('CTH - Time and Schedule Control') . ' | ' . __('Page');

        return Browsershot::html($html)
            ->format('A4')
            ->landscape()
            ->margins(10, 10, 15, 10)
            ->showBackground()
            ->footerHtml('<div style="font-size: 8pt; text-align: center; width: 100%; color: #9CA3AF;">' . $footerText . ' <span class="pageNumber"></span> ' . __('of') . ' <span class="totalPages"></span></div>')
            ->pdf();
    }
}
