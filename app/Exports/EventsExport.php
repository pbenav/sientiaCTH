<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $events;

    public function __construct($events)
    {
        $this->events = $events;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->events;
    }

    /**
     * @var Event $event
     */
    public function map($event): array
    {
        return [
            $event->user->name . " " . $event->user->family_name1 . " " . $event->user->family_name2,
            $event->id,
            $event->start,
            $event->end,
            $event->getPeriod(),
            $event->description,
            $event->observations,
        ];
    }

    public function headings(): array
    {
        return [
            __('Name'),
            __('Event'),
            __('Start date'),
            __('End date'),
            __('Duration'),
            __('Description'),
            __('Observations')
        ];
    }
}
