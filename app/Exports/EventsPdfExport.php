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

        return Browsershot::html($html)
            ->format('A4')
            ->landscape()
            ->margins(10, 10, 15, 10)
            ->showBackground()
            ->footerHtml('<div style="font-size: 8pt; text-align: center; width: 100%; color: #9CA3AF;">CTH - Control de Tiempo y Horarios | Página <span class="pageNumber"></span> de <span class="totalPages"></span></div>')
            ->pdf();
    }
}
