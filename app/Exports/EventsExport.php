<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EventsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $events;

    public function __construct($events)
    {
        $this->events = $events;
    }

    public function collection()
    {
        return $this->events;
    }

    public function headings(): array
    {
        return [
            __('Name'),
            __('Start'),
            __('End'),
            __('Duration'),
            __('Description'),
            __('Observations'),
        ];
    }

    public function map($event): array
    {
        return [
            $event->user->name . ' ' . $event->user->family_name1,
            \Carbon\Carbon::parse($event->start)->format('d/m/Y H:i'),
            \Carbon\Carbon::parse($event->end)->format('d/m/Y H:i'),
            $event->getPeriod(),
            $event->description,
            $event->observations,
        ];
    }
}
