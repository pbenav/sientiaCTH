<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Holiday;
use App\Traits\HandlesEventAuthorization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * A Livewire component that provides a full calendar view of events.
 *
 * This component is responsible for rendering the calendar, fetching events,
 * and handling user interactions such as dragging, dropping, and resizing
 * events.
 */
class Calendar extends Component
{
    use HandlesEventAuthorization;

    /**
     * The event listeners for the component.
     *
     * @var array
     */
    protected $listeners = [
        'refreshCalendar' => 'refresh',
        'eventDrop' => 'eventDrop',
        'eventResize' => 'eventResize'
    ];

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.calendar');
    }

    /**
     * Refresh the calendar with the latest events.
     *
     * @return void
     */
    public function refresh(): void
    {
        $this->dispatchBrowserEvent('refresh-calendar', ['events' => $this->getEvents()]);
    }

    /**
     * Handle the event drop event.
     *
     * @param int $eventId
     * @param string $newStart
     * @param string|null $newEnd
     * @return void
     */
    public function eventDrop(int $eventId, string $newStart, ?string $newEnd): void
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

    /**
     * Handle the event resize event.
     *
     * @param int $eventId
     * @param string $newStart
     * @param string $newEnd
     * @return void
     */
    public function eventResize(int $eventId, string $newStart, string $newEnd): void
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

    /**
     * Trigger the edit event modal.
     *
     * @param int $eventId
     * @return void
     */
    public function triggerEditModal(int $eventId): void
    {
        $this->emit('edit', $eventId);
    }

    /**
     * Trigger the add event modal.
     *
     * @param array $origin
     * @return void
     */
    public function triggerAddModal(array $origin): void
    {
        $this->emit('add', $origin);
    }

    /**
     * Get all events for the current user's team.
     *
     * @return \Illuminate\Support\Collection
     */
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
            ->map(function ($event) use ($teamTimezone) {
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
