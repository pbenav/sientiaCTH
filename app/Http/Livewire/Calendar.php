<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class Calendar extends Component
{
    protected $listeners = ['refreshCalendar' => 'refresh', 'eventDrop' => 'eventDrop'];

    public function render()
    {
        return view('livewire.calendar');
    }

    public function refresh()
    {
        $this->dispatchBrowserEvent('refresh-calendar', ['events' => $this->getEvents()]);
    }

    public function eventDrop($eventId, $newStart, $newEnd)
    {
        $event = Event::find($eventId);
        if ($event) {
            $event->update([
                'start' => $newStart,
                'end' => $newEnd,
            ]);
        }
    }

    public function getEvents()
    {
        $events = Event::with('eventType')
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->description,
                    'start' => $event->start,
                    'end' => $event->end,
                    'color' => $event->eventType->color ?? '#3788d8', // Default color
                    'allDay' => $event->eventType->is_all_day ?? false,
                ];
            });

        return $events;
    }
}
