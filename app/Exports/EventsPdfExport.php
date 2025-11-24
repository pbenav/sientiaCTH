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

        $footerText = trans('reports.CTH - Time and Schedule Control') . ' | ' . trans('reports.Page');
        
        return Browsershot::html($html)
            ->format('A4')
            ->landscape()
            ->margins(10, 10, 20, 10) // Increased bottom margin for footer
            ->showBackground()
            ->setOption('displayHeaderFooter', true)
            ->setOption('headerTemplate', '<div></div>') // Empty header
            ->setOption('footerTemplate', '<div style="font-size: 8pt; text-align: center; width: 100%; color: #9CA3AF; padding-top: 5px;">' . 
                $footerText . ' <span class="pageNumber"></span> ' . trans('reports.of') . ' <span class="totalPages"></span>' .
                '</div>')
            ->pdf();
    }
}
