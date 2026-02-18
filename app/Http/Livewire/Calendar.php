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

    public $refreshKey;
    
    // Adjustment modal properties
    public bool $showAdjustmentModal = false;
    public int $maxMinutes = 0;
    public int $currentMinutes = 0;
    public ?int $pendingEventId = null;
    public ?string $pendingAction = null; // 'drop' or 'resize'
    public array $pendingData = [];

    public function mount()
    {
        $this->refreshKey = now()->timestamp;
    }

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

        try {
            $schedule = json_decode($scheduleMeta->meta_value, true);
            
            if (empty($schedule) || !is_array($schedule)) {
                return '08:00:00'; // Default fallback
            }
            $earliestTime = null;
            
            // Find the earliest start time across all days and time slots
            foreach ($schedule as $dayKey => $daySchedule) {
                if (isset($daySchedule['slots']) && is_array($daySchedule['slots'])) {
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
                $scrollTime = $this->minutesToTimeString($scrollTimeMinutes);
                return $scrollTime;
            }
            
            return '08:00:00'; // Default fallback
            
        } catch (\Exception $e) {
            return '08:00:00'; // Default fallback
        }
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
        \Log::info('Calendar::refresh called - forcing full page reload');
        // Force a full page reload to ensure everything is in sync
        $this->dispatchBrowserEvent('reload-page');
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
                // CRITICAL: Save original values BEFORE update for potential rollback
                $originalStart = $event->start;
                $originalEnd = $event->end;
                
                try {
                    if ($event->eventType && $event->eventType->is_all_day) {
                        // For all-day events, ALWAYS keep them as all-day, ignore time components
                        $startDate = Carbon::parse($newStart)->format('Y-m-d');
                        $endDate = $newEnd ? Carbon::parse($newEnd)->format('Y-m-d') : null;
                        
                        // For all-day events, FullCalendar sends exclusive end dates
                        // Check if it's a multi-day event (end is provided and different from start)
                        if ($endDate && $startDate !== $endDate) {
                            // Subtract one day to get the actual last day (FullCalendar uses exclusive end)
                            $endDate = Carbon::parse($endDate)->subDay()->format('Y-m-d');
                        }
                        
                        $event->update([
                            'start' => $startDate . ' 00:00:00',
                            'end' => $endDate ? $endDate . ' 00:00:00' : $startDate . ' 00:00:00',
                        ]);
                        $this->refresh(); // Only refresh on success
                    } else {
                        // For timed events, convert from team timezone to UTC
                        $event->update([
                            'start' => Carbon::parse($newStart, $teamTimezone)->setTimezone('UTC'),
                            'end' => $newEnd ? Carbon::parse($newEnd, $teamTimezone)->setTimezone('UTC') : null,
                        ]);
                    }

                    // Block workday events from being moved to future dates
                    if ($event->eventType && $event->eventType->is_workday_type) {
                        $newStartInTeamTz = Carbon::parse($event->start, 'UTC')->setTimezone($teamTimezone);
                        if ($newStartInTeamTz->isFuture() && $newStartInTeamTz->isAfter(Carbon::now($teamTimezone)->addMinutes(5))) {
                            $event->update([
                                'start' => $originalStart,
                                'end'   => $originalEnd,
                            ]);
                            session()->flash('alert-fail', __('No se pueden mover eventos de jornada laboral a fechas futuras.'));
                            $this->refresh();
                            return;
                        }
                    }

                    // CRITICAL: Validate daily total AFTER moving event
                    // The observer only validates individual event, not daily sum
                    if ($event->end && $event->user && $event->eventType && $event->eventType->is_workday_type && !$event->is_exceptional) {
                        $service = app(\App\Services\SmartClockInService::class);
                        $validation = $service->validateMaxDuration($event->user, $event, $event->end);
                        
                        if (!$validation['success'] && 
                            isset($validation['status_code']) && 
                            $validation['status_code'] === \App\Services\SmartClockInService::STATUS_MAX_DURATION_EXCEEDED) {
                            
                            // MANUAL ROLLBACK: Restore original values
                            $event->update([
                                'start' => $originalStart,
                                'end' => $originalEnd,
                            ]);
                            
                            // Show error message
                            session()->flash('alert-fail', __(
                                'No se puede mover el evento. El total del día excedería el límite (:max min). Total resultante: :current min.',
                                [
                                    'max' => $validation['max_minutes'],
                                    'current' => $validation['current_minutes']
                                ]
                            ));
                            
                            $this->refresh();
                            return;
                        }
                    }
                    
                    $this->refresh(); // Only refresh on success
                } catch (\App\Exceptions\MaxWorkdayDurationExceededException $e) {
                    // Store pending data for adjustment modal
                    $this->pendingEventId = $eventId;
                    $this->pendingAction = 'drop';
                    $this->pendingData = [
                        'newStart' => $newStart,
                        'newEnd' => $newEnd,
                    ];
                    $this->maxMinutes = $e->maxMinutes;
                    $this->currentMinutes = $e->currentMinutes;
                    $this->showAdjustmentModal = true;
                    // DO NOT refresh - let user choose adjustment option
                }
            }
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
                // CRITICAL: Save original values BEFORE update for potential rollback
                $originalStart = $event->start;
                $originalEnd = $event->end;
                
                try {
                    if ($event->eventType && $event->eventType->is_all_day) {
                        // For all-day events, store pure dates in UTC without timezone conversion
                        $startDate = Carbon::parse($newStart)->format('Y-m-d');
                        $endDate = Carbon::parse($newEnd)->format('Y-m-d');
                        
                        // For all-day events, FullCalendar sends exclusive end dates
                        // Check if it's a multi-day event (end is different from start)
                        if ($startDate !== $endDate) {
                            // Subtract one day to get the actual last day (FullCalendar uses exclusive end)
                            $endDate = Carbon::parse($endDate)->subDay()->format('Y-m-d');
                        }
                        
                        $event->update([
                            'start' => $startDate . ' 00:00:00',
                            'end' => $endDate . ' 00:00:00',
                        ]);
                        $this->refresh(); // Only refresh on success
                    } else {
                        // For timed events, convert from team timezone to UTC
                        $event->update([
                            'start' => Carbon::parse($newStart, $teamTimezone)->setTimezone('UTC'),
                            'end' => Carbon::parse($newEnd, $teamTimezone)->setTimezone('UTC'),
                        ]);
                    }

                    // Block workday events from being resized into future dates
                    if ($event->eventType && $event->eventType->is_workday_type) {
                        $newEndInTeamTz = Carbon::parse($event->end, 'UTC')->setTimezone($teamTimezone);
                        if ($newEndInTeamTz->isFuture() && $newEndInTeamTz->isAfter(Carbon::now($teamTimezone)->addMinutes(5))) {
                            $event->update([
                                'start' => $originalStart,
                                'end'   => $originalEnd,
                            ]);
                            session()->flash('alert-fail', __('No se puede extender un evento de jornada laboral a fechas futuras.'));
                            $this->refresh();
                            return;
                        }
                    }

                    // CRITICAL: Validate daily total AFTER resizing event
                    // The observer only validates individual event, not daily sum
                    if ($event->end && $event->user && $event->eventType && $event->eventType->is_workday_type && !$event->is_exceptional) {
                        $service = app(\App\Services\SmartClockInService::class);
                        $validation = $service->validateMaxDuration($event->user, $event, $event->end);
                        
                        if (!$validation['success'] && 
                            isset($validation['status_code']) && 
                            $validation['status_code'] === \App\Services\SmartClockInService::STATUS_MAX_DURATION_EXCEEDED) {
                            
                            // MANUAL ROLLBACK: Restore original values
                            $event->update([
                                'start' => $originalStart,
                                'end' => $originalEnd,
                            ]);
                            
                            // Show error message
                            session()->flash('alert-fail', __(
                                'No se puede redimensionar el evento. El total del día excedería el límite (:max min). Total resultante: :current min.',
                                [
                                    'max' => $validation['max_minutes'],
                                    'current' => $validation['current_minutes']
                                ]
                            ));
                            
                            $this->refresh();
                            return;
                        }
                    }
                    
                    $this->refresh(); // Only refresh on success
                } catch (\App\Exceptions\MaxWorkdayDurationExceededException $e) {
                // Store pending data for adjustment modal
                $this->pendingEventId = $eventId;
                $this->pendingAction = 'resize';
                $this->pendingData = [
                    'newStart' => $newStart,
                    'newEnd' => $newEnd,
                ];
                $this->maxMinutes = $e->maxMinutes;
                $this->currentMinutes = $e->currentMinutes;
                $this->showAdjustmentModal = true;
                // DO NOT refresh - let user choose adjustment option
            }
            }
        }
    }

    /**
     * Add a newly created event to the calendar without full refresh.
     *
     * @param int $eventId
     * @return void
     */
    /**
     * Trigger the edit event modal.
     *
     * @param int $eventId
     * @return void
     */
    public function triggerEditModal(int $eventId): void
    {
        $this->emit('edit', $eventId, 'calendar');
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
        $event = Event::with(['eventType', 'team', 'workCenter', 'user'])
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

        // Get user events from current team
        $userEvents = Event::with('eventType')
            ->where('user_id', $user->id)
            ->where('team_id', $user->currentTeam->id)
            ->get()
            ->map(function ($eventModel) use ($teamTimezone, $user) {
                $iconHtml = $eventModel->is_open
                    ? '<i class="ml-1 mr-2 fa-solid fa-lock-open" style="color: #28a745;"></i>'
                    : '<i class="ml-1 mr-2 fa-solid fa-lock" style="color: #dc3545;"></i>';

                // Determine event color based on type and properties
                $eventColor = '#3788d8'; // Default color
                
                if ($eventModel->is_exceptional) {
                    // Use special event color if event is exceptional
                    $eventColor = $user->currentTeam->special_event_color ?? '#DC2626';
                } elseif ($eventModel->eventType) {
                    if ($eventModel->eventType->color) {
                        // Use event type color if available
                        $eventColor = $eventModel->eventType->color;
                    } elseif (!$eventModel->eventType->is_workday_type) {
                        // Use special event color for non-workday types without specific color
                        $eventColor = $user->currentTeam->special_event_color ?? '#EA8000';
                    }
                } else {
                    // Use special event color for events without type
                    $eventColor = $user->currentTeam->special_event_color ?? '#EA8000';
                }
                
                // Prepare end date for calendar display
                $endDate = null;
                if ($eventModel->end) {
                    if ($eventModel->eventType && $eventModel->eventType->is_all_day) {
                        $startDate = Carbon::parse($eventModel->start, 'UTC')->startOfDay();
                        $endDateCarbon = Carbon::parse($eventModel->end, 'UTC')->startOfDay();
                        
                        // Only set end if it's a multi-day event
                        // For single-day events, leave end as null
                        if (!$startDate->isSameDay($endDateCarbon)) {
                            // For multi-day all-day events, FullCalendar expects exclusive end dates (next day)
                            $endDate = $endDateCarbon->addDay()->format('Y-m-d');
                        }
                    } else {
                        // For timed events, convert to team timezone
                        $endDate = Carbon::parse($eventModel->end, 'UTC')->setTimezone($teamTimezone)->toIso8601String();
                    }
                }
                
                return [
                    'id' => 'event_' . $eventModel->id,
                    'title' => $eventModel->description,
                    'iconHtml' => $iconHtml,
                    'start' => $eventModel->eventType && $eventModel->eventType->is_all_day
                        ? Carbon::parse($eventModel->start, 'UTC')->format('Y-m-d')
                        : Carbon::parse($eventModel->start, 'UTC')->setTimezone($teamTimezone)->toIso8601String(),
                    'end' => $endDate,
                    'color' => $eventColor,
                    'allDay' => $eventModel->eventType->is_all_day ?? false,
                    'editable' => $eventModel->is_open && ($user->ownsTeam($user->currentTeam) || $user->hasTeamRole($user->currentTeam, 'admin') || $eventModel->user_id === $user->id),
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
                    // Holidays are single-day events, no end date needed
                    'color' => '#ff6b35',
                    'allDay' => true,
                ];
            });

        return collect(array_merge($userEvents->all(), $holidays->all()));
    }

    /**
     * Apply adjustment to pending event
     */
    public function applyAdjustment($type)
    {
        if (!$this->pendingEventId || !$this->pendingAction) {
            return;
        }

        $event = Event::find($this->pendingEventId);
        if (!$event) {
            $this->showAdjustmentModal = false;
            return;
        }

        $service = app(\App\Services\SmartClockInService::class);
        $teamTimezone = Auth::user()->currentTeam->timezone ?? config('app.timezone');
        
        if ($type === 'adjust_schedule') {
            // Use SmartClockInService for schedule distribution
            $result = $service->clockOutWithAdjustment(
                $event->user,
                $event->id,
                'adjust_schedule'
            );
            
            if ($result['success']) {
                $this->showAdjustmentModal = false;
                $this->reset(['pendingEventId', 'pendingAction', 'pendingData']);
                $this->refresh();
                session()->flash('alert-success', $result['message']);
            }
        } else {
            // Handle adjust_start and adjust_end
            $maxMinutes = $this->maxMinutes;
            $teamTimezone = Auth::user()->currentTeam->timezone ?? config('app.timezone');
            
            // IMPORTANT: Use event's original DATE, not pendingData dates
            // pendingData has the attempted drag/resize position which may have wrong date
            $eventStart = Carbon::parse($event->start)->setTimezone($teamTimezone);
            $eventEnd = Carbon::parse($event->end)->setTimezone($teamTimezone);
            
            $newStart = $eventStart;
            $newEnd = $eventEnd;
            
            if ($type === 'adjust_start') {
                // Keep end time, adjust start backwards
                $newStart = $eventEnd->copy()->subMinutes($maxMinutes);
            } elseif ($type === 'adjust_end') {
                // Keep start time, adjust end forwards
                $newEnd = $eventStart->copy()->addMinutes($maxMinutes);
            }
            
            $event->update([
                'start' => $newStart->setTimezone('UTC'),
                'end' => $newEnd->setTimezone('UTC'),
                'observations' => ($event->observations ? $event->observations . "\n" : "") .
                    __('Ajuste automático desde calendario (:type, :minutes min)', [
                        'type' => $type,
                        'minutes' => $maxMinutes
                    ])
            ]);
            
            $this->showAdjustmentModal = false;
            $this->reset(['pendingEventId', 'pendingAction', 'pendingData']);
            $this->refresh();
        }
    }

    /**
     * Cancel adjustment and revert changes
     */
    public function cancelAdjustment()
    {
        $this->showAdjustmentModal = false;
        $this->reset(['pendingEventId', 'pendingAction', 'pendingData', 'maxMinutes', 'currentMinutes']);
        $this->refresh(); // Refresh calendar to revert visual change
    }
}
