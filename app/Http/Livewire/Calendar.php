<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Holiday;
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

        // Get user events
        $userEvents = Event::with('eventType')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($event) {
                $color = $event->override_color ?? $event->eventType->color ?? '#3788d8';

                return [
                    'id' => 'event_' . $event->id,
                    'title' => $event->description,
                    'start' => Carbon::parse($event->start, 'UTC')->toIso8601String(),
                    'end' => $event->end ? Carbon::parse($event->end, 'UTC')->toIso8601String() : null,
                    'color' => $color,
                    'allDay' => $event->eventType->is_all_day ?? false,
                    'is_open' => $event->is_open,
                ];
            });


        // Get team holidays
        $holidays = Holiday::where('team_id', $user->currentTeam->id)
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => 'holiday_' . $holiday->id,
                    'title' => $holiday->name,
                    'start' => $holiday->date->format('Y-m-d'),
                    'end' => $holiday->date->format('Y-m-d'),
                    'color' => '#A3E635',
                    'allDay' => true,
                    'is_holiday' => true,
                ];
            });

        return $userEvents->merge($holidays);
    }
}
