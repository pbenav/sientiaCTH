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
        $weekStartsOn = Auth::user()->week_starts_on ?? 1; // Default to Monday
        $scrollTime = $this->getOptimalScrollTime();
        
        return view('livewire.calendar', [
            'weekStartsOn' => $weekStartsOn,
            'scrollTime' => $scrollTime
        ]);
    }

    /**
     * Get optimal scroll time based on user's work schedule
     */
    private function getOptimalScrollTime(): string
    {
        $user = Auth::user();
        
        if (!$user) {
            return '08:00:00'; // Default fallback
        }

        // Get work schedule from user meta
        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        
        if (!$scheduleMeta || !$scheduleMeta->meta_value) {
            return '08:00:00'; // Default fallback
        }

        $schedule = json_decode($scheduleMeta->meta_value, true);
        
        if (empty($schedule)) {
            return '08:00:00'; // Default fallback
        }

        $earliestTime = null;
        
        // Find the earliest start time across all days and time slots
        foreach ($schedule as $daySchedule) {
            if (!empty($daySchedule['slots'])) {
                foreach ($daySchedule['slots'] as $slot) {
                    if (isset($slot['start']) && !empty($slot['start'])) {
                        $startTime = $slot['start'];
                        
                        // Convert to minutes for comparison
                        $timeInMinutes = $this->timeToMinutes($startTime);
                        
                        if ($earliestTime === null || $timeInMinutes < $earliestTime) {
                            $earliestTime = $timeInMinutes;
                        }
                    }
                }
            }
        }
        
        if ($earliestTime !== null) {
            // Subtract 30 minutes to show a bit before the start time
            $scrollTimeMinutes = max(0, $earliestTime - 30);
            return $this->minutesToTimeString($scrollTimeMinutes);
        }
        
        return '08:00:00'; // Default fallback
    }

    /**
     * Convert time string (HH:MM) to minutes
     */
    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        $hours = (int)($parts[0] ?? 0);
        $minutes = (int)($parts[1] ?? 0);
        
        return ($hours * 60) + $minutes;
    }

    /**
     * Convert minutes to time string (HH:MM:SS)
     */
    private function minutesToTimeString(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return sprintf('%02d:%02d:00', $hours, $remainingMinutes);
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
     * Show event information modal for closed events.
     *
     * @param int $eventId
     * @return void
     */
    public function showEventInfo(int $eventId): void
    {
        $event = Event::with(['eventType', 'workCenter', 'user'])
            ->find($eventId);
            
        if ($event) {
            // Format the event data for the modal
            $eventData = $event->toArray();
            
            // Add formatted dates in the user's timezone
            $teamTimezone = Auth::user()->currentTeam->timezone ?? config('app.timezone');
            
            if ($event->start) {
                $eventData['start'] = Carbon::parse($event->start, 'UTC')
                    ->setTimezone($teamTimezone)
                    ->toIso8601String();
            }
            
            if ($event->end) {
                $eventData['end'] = Carbon::parse($event->end, 'UTC')
                    ->setTimezone($teamTimezone)
                    ->toIso8601String();
            }
            
            $this->emit('showEventInfoModal', $eventData);
        }
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
            ->map(function ($eventModel) use ($teamTimezone, $user) {
                $iconHtml = $eventModel->is_open
                    ? '<i class="ml-1 mr-2 fa-solid fa-lock-open" style="color: #28a745;"></i>'
                    : '<i class="ml-1 mr-2 fa-solid fa-lock" style="color: #dc3545;"></i>';

                return [
                    'id' => 'event_' . $eventModel->id,
                    'title' => $eventModel->description,
                    'iconHtml' => $iconHtml,
                    'start' => Carbon::parse($eventModel->start, 'UTC')->setTimezone($teamTimezone)->toIso8601String(),
                    'end' => $eventModel->end ? Carbon::parse($eventModel->end, 'UTC')->setTimezone($teamTimezone)->toIso8601String() : null,
                    'color' => $eventModel->eventType->color ?? '#3788d8',
                    'allDay' => $eventModel->eventType->is_all_day ?? false,
                    'editable' => $eventModel->is_open && ($user->hasTeamRole($user->currentTeam, 'admin') || $eventModel->user_id === $user->id),
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
