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
    protected $startDate;
    protected $endDate;
    protected $teamTimezone;

    public function __construct($events, $startDate = null, $endDate = null, $teamTimezone = 'UTC')
    {
        $this->events = $events;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->teamTimezone = $teamTimezone;
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
        // Parse event dates in team timezone
        $eventStart = \Carbon\Carbon::parse($event->start, 'UTC')->setTimezone($this->teamTimezone);
        $eventEnd = \Carbon\Carbon::parse($event->end, 'UTC')->setTimezone($this->teamTimezone);
        
        // Clip event to report date range if dates are provided
        $clippedStart = $eventStart->copy();
        $clippedEnd = $eventEnd->copy();
        
        if ($this->startDate && $this->endDate) {
            $rangeStart = \Carbon\Carbon::parse($this->startDate, $this->teamTimezone)->startOfDay();
            $rangeEnd = \Carbon\Carbon::parse($this->endDate, $this->teamTimezone)->endOfDay();
            
            // Clip start date if event starts before range
            if ($clippedStart->lt($rangeStart)) {
                $clippedStart = $rangeStart;
            }
            
            // Clip end date if event ends after range
            if ($clippedEnd->gt($rangeEnd)) {
                $clippedEnd = $rangeEnd;
            }
        }
        
        // Format clipped dates for display
        $startFormatted = $clippedStart->format('d/m/Y H:i');
        $endFormatted = $clippedEnd->format('d/m/Y H:i');
        
        // Calculate duration for clipped event using the same logic as calculateAllDayEventDays
        $startDay = $clippedStart->copy()->startOfDay();
        $endDay = $clippedEnd->copy()->startOfDay();
        
        // If event ends at any time other than 00:00:00, include that full day
        if ($clippedEnd->format('H:i:s') !== '00:00:00') {
            $endDay->addDay();
        }
        
        $days = $startDay->diffInDays($endDay);
        $days = $days < 1 ? 1 : $days;
        
        // Format duration
        $duration = $days . ' ' . ($days == 1 ? __('day') : __('days'));
        
        return [
            $event->user->name . ' ' . $event->user->family_name1,
            $startFormatted,
            $endFormatted,
            $duration,
            $event->description,
            $event->observations,
        ];
    }
}
