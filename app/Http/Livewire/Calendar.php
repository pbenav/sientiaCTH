<?php

namespace App\Http\Livewire;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Calendar extends Component
{
    protected $listeners = [
        'refreshCalendar' => 'refresh',
        'eventDrop' => 'eventDrop',
        'eventResize' => 'eventResize'
    ];

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
                'start' => Carbon::parse($newStart)->format('Y-m-d H:i:s'),
                'end' => $newEnd ? Carbon::parse($newEnd)->format('Y-m-d H:i:s') : null,
            ]);
        }
    }

    public function eventResize($eventId, $newStart, $newEnd)
    {
        $event = Event::find($eventId);

        if ($event) {
            $event->update([
                'start' => Carbon::parse($newStart)->format('Y-m-d H:i:s'),
                'end' => Carbon::parse($newEnd)->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function triggerEditModal($eventId)
    {
        $this->emit('edit', $eventId);
    }

    public function triggerAddModal($origin)
    {
        $this->emit('add', $origin);
    }

    public function getEvents()
    {
        $user = Auth::user();
        if (!$user || !$user->currentTeam) {
            return [];
        }

        $events = Event::with('eventType')
            ->where('user_id', $user->id)
            ->whereHas('eventType', function ($query) use ($user) {
                $query->where('team_id', $user->currentTeam->id);
            })
            ->get()
            ->map(function ($event) {
                $iconHtml = $event->is_open
                    ? '<i class="ml-1 mr-2 fa-solid fa-lock-open" style="color: #28a745;"></i>'
                    : '<i class="ml-1 mr-2 fa-solid fa-lock" style="color: #dc3545;"></i>';

                return [
                    'id' => $event->id,
                    'title' => $event->description, // El título ahora es solo el texto
                    'iconHtml' => $iconHtml,       // El icono se pasa en una nueva propiedad
                    'start' => Carbon::parse($event->start, 'UTC')->toIso8601String(),
                    'end' => $event->end ? Carbon::parse($event->end, 'UTC')->toIso8601String() : null,
                    'color' => $event->eventType->color ?? '#3788d8',
                    'allDay' => $event->eventType->is_all_day ?? false,
                ];
            });

        return $events;
    }
}
