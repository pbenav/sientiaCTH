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
            $user = Auth::user();
            if ($user->hasTeamRole($user->currentTeam, 'admin') || $event->is_open) {
                $event->update([
                    'start' => Carbon::parse($newStart)->format('Y-m-d H:i:s'),
                    'end' => $newEnd ? Carbon::parse($newEnd)->format('Y-m-d H:i:s') : null,
                ]);
            }

            $this->refresh();
        }
    }

    public function eventResize($eventId, $newStart, $newEnd)
    {
        $event = Event::find($eventId);

        if ($event) {
            $user = Auth::user();
            if ($user->hasTeamRole($user->currentTeam, 'admin') || $event->is_open) {
                $event->update([
                    'start' => Carbon::parse($newStart)->format('Y-m-d H:i:s'),
                    'end' => Carbon::parse($newEnd)->format('Y-m-d H:i:s'),
                ]);
            }

            $this->refresh();
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

        $events = collect();

        // Get user events
        $userEvents = Event::with('eventType')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($event) use ($user) {
                $iconHtml = $event->is_open
                    ? '<i class="ml-1 mr-2 fa-solid fa-lock-open" style="color: #28a745;"></i>'
                    : '<i class="ml-1 mr-2 fa-solid fa-lock" style="color: #dc3545;"></i>';

                $editable = $user->hasTeamRole($user->currentTeam, 'admin') || $event->is_open;

                return [
                    'id' => 'event_' . $event->id,
                    'title' => $event->description,
                    'iconHtml' => $iconHtml,
                    'start' => Carbon::parse($event->start, 'UTC')->toIso8601String(),
                    'end' => $event->end ? Carbon::parse($event->end, 'UTC')->toIso8601String() : null,
                    'color' => $event->eventType->color ?? '#3788d8',
                    'allDay' => $event->eventType->is_all_day ?? false,
                    'editable' => $editable,
                ];
            });

        // Get team holidays
        $holidays = Holiday::where('team_id', $user->currentTeam->id)
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => 'holiday_' . $holiday->id,
                    'title' => $holiday->name,
                    'iconHtml' => '<i class="ml-1 mr-2 fa-solid fa-calendar-day" style="color: #ff6b35;"></i>',
                    'start' => $holiday->date->format('Y-m-d'),
                    'end' => $holiday->date->format('Y-m-d'),
                    'color' => '#ff6b35',
                    'allDay' => true,
                ];
            });

        return $userEvents->merge($holidays);
    }
}
