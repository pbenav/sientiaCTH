<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Holiday;
use App\Traits\HandlesEventAuthorization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Calendar extends Component
{
    use HandlesEventAuthorization;

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
        $teamTimezone = Auth::user()->currentTeam->timezone ?? config('app.timezone');

        if ($event) {
            if ($this->canModifyEvent($event)) {
                $event->update([
                    'start' => Carbon::parse($newStart, $teamTimezone)->setTimezone('UTC'),
                    'end' => $newEnd ? Carbon::parse($newEnd, $teamTimezone)->setTimezone('UTC') : null,
                ]);
            }
            $this->refresh();
        }
    }

    public function eventResize($eventId, $newStart, $newEnd)
    {
        $event = Event::find($eventId);
        $teamTimezone = Auth::user()->currentTeam->timezone ?? config('app.timezone');

        if ($event) {
            if ($this->canModifyEvent($event)) {
                $event->update([
                    'start' => Carbon::parse($newStart, $teamTimezone)->setTimezone('UTC'),
                    'end' => Carbon::parse($newEnd, $teamTimezone)->setTimezone('UTC'),
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

        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');

        $events = collect();

        // Get user events
        $userEvents = Event::with('eventType')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($event) {
                $iconHtml = $event->is_open
                    ? '<i class="ml-1 mr-2 fa-solid fa-lock-open" style="color: #28a745;"></i>'
                    : '<i class="ml-1 mr-2 fa-solid fa-lock" style="color: #dc3545;"></i>';

                return [
                    'id' => 'event_' . $event->id,
                    'title' => $event->description,
                    'iconHtml' => $iconHtml,
                    'start' => Carbon::parse($event->start, 'UTC')->setTimezone($teamTimezone)->toIso8601String(),
                    'end' => $event->end ? Carbon::parse($event->end, 'UTC')->setTimezone($teamTimezone)->toIso8601String() : null,
                    'color' => $event->eventType->color ?? '#3788d8',
                    'allDay' => $event->eventType->is_all_day ?? false,
                    'editable' => $this->canModifyEvent($event),
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

        return collect(array_merge($userEvents->all(), $holidays->all()));
    }
}
