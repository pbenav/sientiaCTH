<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\EventType;
use App\Models\ExceptionalClockInToken;
use App\Models\Message;
use App\Notifications\EventCreated;
use App\Notifications\NewMessage;
use App\Traits\HasWorkScheduleHint;
use App\Traits\HandlesEventAuthorization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * A Livewire component for adding new events.
 *
 * This component provides a modal form for creating new events, including
 * handling for exceptional clock-ins and different event types.
 */
class AddEvent extends Component
{
    use HasWorkScheduleHint;
    use HandlesEventAuthorization;

    public bool $showAddEventModal = false;
    public string $workScheduleHint = '';
    public bool $goDashboardModal = false;
    public $now;
    public string $start_date;
    public string $end_date;
    public string $start_time;
    public string $end_time;
    public ?int $user_id = null;
    public string $description = '';
    public $event_type_id = null;
    public $eventTypes;
    public ?EventType $selectedEventType = null;
    public string $observations = '';
    public string $origin;
    public ?float $latitude = null;
    public ?float $longitude = null;
    protected $listeners = ['add'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function rules(): array
    {
        $rules = [
            'event_type_id' => 'required',
            'start_date' => 'required|date',
            'observations' => 'nullable|string|max:255',
        ];

        if ($this->selectedEventType && $this->selectedEventType->is_all_day) {
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        } else if ($this->selectedEventType) {
            $rules['start_time'] = 'required';
        }

        return $rules;
    }

    /**
     * Validate a single property.
     *
     * @param string $propertyName
     * @return void
     */
    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Handle the update of the event_type_id property.
     *
     * @param mixed $value
     * @return void
     */
    public function updatedEventTypeId($value): void
    {
        if (empty($value)) {
            $this->event_type_id = null;
            $this->selectedEventType = null;
        } else {
            $this->event_type_id = (int) $value;
            $this->selectedEventType = $this->eventTypes->firstWhere('id', $this->event_type_id);
        }

        // Update description with event type name if it matches any existing event type name or is the default
        $isEventTypeName = $this->eventTypes->pluck('name')->contains($this->description);
        if (empty($this->description) || $this->description === __('Workday') || $isEventTypeName) {
            $this->description = $this->selectedEventType ? $this->selectedEventType->name : '';
        }
    }

    /**
     * Mount the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->start_date = date('Y-m-d');
        $this->end_date = date('Y-m-d');
        $this->start_time = date('H:i:s');
        $this->end_time = ''; // Empty by default - events are created OPEN
        $this->description = __('Workday');
        $this->observations = '';
        $this->eventTypes = collect();
        $this->event_type_id = null;
        $this->selectedEventType = null;

        if (Auth::check()) {
            $this->setWorkScheduleHint();
        }
    }

    /**
     * Show the add event modal.
     *
     * @param array|string $data
     * @return void
     */
    public function add($data): void
    {
        $this->reset(['description', 'observations', 'event_type_id', 'selectedEventType']);
        if (isset($data['date'])) {
            $date = \Carbon\Carbon::parse($data['date']);
            $this->start_date = $date->format('Y-m-d');
            $this->end_date = $date->format('Y-m-d');
            $this->start_time = $date->format('H:i:s');
            $this->end_time = ''; // Empty - create OPEN events
        } else {
            $this->start_date = date('Y-m-d');
            $this->end_date = date('Y-m-d');
            $this->start_time = date('H:i:s');
            $this->end_time = ''; // Empty - create OPEN events
        }
        $this->description = __('Workday');

        if (Auth::check() && Auth::user()->currentTeam) {
            $this->eventTypes = Auth::user()->currentTeam->eventTypes;
            if ($this->eventTypes->count() > 0) {
                $this->event_type_id = $this->eventTypes->first()->id;
                $this->selectedEventType = $this->eventTypes->first();
            }
        } else {
            $this->eventTypes = collect();
        }

        $this->setWorkScheduleHint();
        $this->origin = is_array($data) ? $data['origin'] : $data;
        $this->showAddEventModal = true;
        
        // Notify JavaScript to capture GPS
        $this->dispatchBrowserEvent('show-add-event-modal');
    }

    /**
     * Close the add event modal.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->showAddEventModal = false;
        if ($this->origin !== 'calendar') {
            $this->redirect('/events');
        }
    }

    /**
     * Save the new event.
     *
     * @param float|null $gpsLatitude
     * @param float|null $gpsLongitude
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public $showAdjustmentModal = false;
    public $maxMinutes = 0;
    public $currentMinutes = 0;
    public bool $isExceptionalOverride = false; // Set to true when a past event exceeds daily limit

    /**
     * Save the new event.
     *
     * @param float|null $gpsLatitude
     * @param float|null $gpsLongitude
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function save($gpsLatitude = null, $gpsLongitude = null)
    {
        \Log::info('AddEvent::save called', [
            'gpsLatitude' => $gpsLatitude,
            'gpsLongitude' => $gpsLongitude,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'event_type_id' => $this->event_type_id,
            'origin' => $this->origin
        ]);
        
        // Override latitude/longitude with GPS parameters if provided
        if ($gpsLatitude !== null && $gpsLongitude !== null) {
            $this->latitude = $gpsLatitude;
            $this->longitude = $gpsLongitude;
        }
        
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;
        $appTimezone = config('app.timezone');

        // Block future events for WORKDAY types only:
        // Holidays, sick leave, etc. can legitimately be registered in the future.
        $isWorkdayType = $this->selectedEventType && $this->selectedEventType->is_workday_type;

        // IMPORTANT: The user enters times in the team's local timezone, not the server timezone.
        // We must parse and compare using the team timezone to avoid false "future" detections.
        $teamTimezone = $team->timezone ?? $appTimezone;
        $eventStartTime = Carbon::parse($this->start_date . ' ' . $this->start_time, $teamTimezone);
        $nowInTeamTz = Carbon::now($teamTimezone);

        if ($isWorkdayType && $eventStartTime->isAfter($nowInTeamTz->copy()->addMinutes(5))) {
            $this->showAddEventModal = false;
            $this->dispatchBrowserEvent('alertFail', [
                'message' => __('No se pueden registrar fichajes de jornada laboral en fechas futuras.')
            ]);
            if ($this->origin === 'calendar') {
                $this->emit('refreshCalendar');
            }
            return;
        }


    // IMPORTANT: Check if the user is CURRENTLY within their work schedule
    // We use the current time (now) to determine if this is an exceptional registration.
    $currentTime = Carbon::now($appTimezone);
    
    $forceDelay = $team->force_clock_in_delay ?? false;

    // Only perform strict checks if force delay is enabled
    if ($forceDelay && $this->selectedEventType && $this->selectedEventType->is_workday_type) {
        // Check strict entry window
        $isAllowed = $this->isWithinEntryWindow($currentTime);

        if (!$isAllowed) {
            // If outside the schedule AND force delay is enabled, trigger the exceptional clock-in flow
            $token = Str::random(60);
            ExceptionalClockInToken::create([
                'user_id' => $user->id,
                'team_id' => $team->id,
                'token' => $token,
                'expires_at' => now()->addMinutes($team->clock_in_grace_period_minutes ?? 10),
            ]);

            $adminSender = $team->owner;
            $url = route('exceptional.clock-in.form', ['token' => $token]);
            $messageContent = __('exceptional_clock_in.message_content', [
                'minutes' => $team->clock_in_grace_period_minutes ?? 10,
                'url' => $url
            ]);

            $message = Message::create([
                'sender_id' => $adminSender->id,
                'subject' => __('exceptional_clock_in.message_subject'),
                'body' => $messageContent,
                'is_log' => true,
            ]);

            $message->recipients()->attach($user->id);
            $user->notify(new NewMessage($message));

            if ($this->origin === 'numpad') {
                $this->showAddEventModal = false;
                return redirect()->route('events')->with('alertFail', __('exceptional_clock_in.validation_error'));
            } else {
                $this->dispatchBrowserEvent('alertFail', ['message' => __('exceptional_clock_in.validation_error')]);
                $this->showAddEventModal = false;
                $this->emit('refreshCalendar');
            }
            return;
        }
    }

        // NEW LOGIC: All time not within main workday events should be considered overtime
        // This includes holidays, weekends, and non-workday event types
        $isExtraHours = !($this->selectedEventType && $this->selectedEventType->is_workday_type);
        
        // If force_clock_in_delay is disabled, we treat all allowed registrations as normal (not exceptional)
        // But respect the override flag set when a past event is marked exceptional due to exceeding daily limit
        $isExceptional = $this->isExceptionalOverride;

        $defaultWorkCenter = $user->meta->where('meta_key', 'default_work_center_id_team_' . $team->id)->first();
        $defaultWorkCenterId = ($defaultWorkCenter && !empty($defaultWorkCenter->meta_value)) ? $defaultWorkCenter->meta_value : null;

        $data = [
            'user_id' => Auth::user()->id,
            'team_id' => $team->id,
            'work_center_id' => $defaultWorkCenterId,
            'description' => !empty($this->description) ? $this->description : $this->selectedEventType->name,
            'observations' => $this->observations,
            'event_type_id' => $this->event_type_id,
            'is_open' => true,
            'is_authorized' => false,
            'is_extra_hours' => $isExtraHours,
            'is_exceptional' => $isExceptional,
        ];

        if ($this->selectedEventType && $this->selectedEventType->is_all_day) {
            // For all-day events, store pure dates in UTC without timezone conversion
            // User enters 2025-03-05, should be stored as 2025-03-05 00:00:00 UTC (not converted)
            // This ensures the event displays on the correct calendar day regardless of timezone
            // Parse directly as UTC date without any timezone conversion
            $data['start'] = Carbon::createFromFormat('Y-m-d', $this->start_date, 'UTC')->startOfDay()->toDateTimeString();
            $data['end'] = Carbon::createFromFormat('Y-m-d', $this->end_date, 'UTC')->startOfDay()->toDateTimeString();
        } else {
            $data['start'] = Carbon::parse($this->start_date . ' ' . $this->start_time, $teamTimezone)->setTimezone('UTC');
            // AddEvent creates OPEN events by default (Clock In behavior)
            // The form doesn't show end time fields for regular events
            $data['end'] = null;
            $data['is_open'] = true;
        }

        if (Schema::hasColumn('events', 'is_authorized')) {
            $data['is_authorized'] = false;
        }

        // Add geolocation data if available and user has it enabled
        if ($user->geolocation_enabled && $this->latitude !== null && $this->longitude !== null) {
            $data['latitude'] = $this->latitude;
            $data['longitude'] = $this->longitude;
        }

        try {
            $event = Event::create($data);
        } catch (\App\Exceptions\MaxWorkdayDurationExceededException $e) {
            $this->showAdjustmentModal = true;
            $this->maxMinutes = $e->maxMinutes;
            $this->currentMinutes = $e->currentMinutes;
            // Keep the modal open
            return;
        }
        
        \Log::info('AddEvent::save - Event created', [
            'event_id' => $event->id,
            'start' => $event->start,
            'end' => $event->end,
            'is_all_day' => $event->eventType ? $event->eventType->is_all_day : false
        ]);

        if ($isExtraHours) {
            session()->flash('info', __('The event has been registered as overtime as it was not found in a defined time slot.'));
        }

        if ($event->eventType && $event->eventType->is_all_day) {
            $team = $event->user->currentTeam;

            if ($team) {
                $admins = $team->allUsers()->filter(function ($user) use ($team) {
                    return $user->hasTeamRole($team, 'admin');
                });

                if ($admins && $admins->isNotEmpty()) {
                    Notification::send($admins, new EventCreated($event));
                }
            }
        }

        $this->reset(['showAddEventModal']);
        $this->showAdjustmentModal = false;
        $this->isExceptionalOverride = false;

        if ($this->origin == 'numpad') {
            return redirect()->route('events')->with('info', 'E_SUCCESS');
        } elseif ($this->origin == '1') {
            return redirect()->route('events')->with('info', 'E_SUCCESS');
        } else {
            $this->emitTo('get-time-registers', 'render');
            
            // Refresh the entire calendar component to keep Livewire in sync
            $this->emit('refreshCalendar');
        }
    }

    public function applyAdjustment($type)
    {
        $maxMinutes = $this->maxMinutes;
        
        // We need to work with the Carbon objects relative to the team timezone to adjust properly
        $startCarbon = Carbon::parse($this->start_date . ' ' . $this->start_time);
        
        // Calculate end based on start for calculations (user input)
        $endCarbon = Carbon::parse($this->end_date . ' ' . $this->end_time);

        switch ($type) {
            case 'adjust_start':
                // Move start forward: newStart = end - maxMinutes
                $newStart = $endCarbon->copy()->subMinutes($maxMinutes);
                
                // Update properties
                $this->start_date = $newStart->format('Y-m-d');
                $this->start_time = $newStart->format('H:i');
                
                // Add observation about adjustment
                if (empty($this->observations)) {
                    $this->observations = '';
                } else {
                    $this->observations .= "\n";
                }
                $this->observations .= __('Ajuste de hora de inicio para cumplir con el máximo de jornada (:minutes min)', ['minutes' => $maxMinutes]);
                break;

            case 'adjust_end':
                // Move end backward: newEnd = start + maxMinutes
                $newEnd = $startCarbon->copy()->addMinutes($maxMinutes);
                
                // Update properties
                $this->end_date = $newEnd->format('Y-m-d');
                $this->end_time = $newEnd->format('H:i');
                
                // Add observation
                if (empty($this->observations)) {
                    $this->observations = '';
                } else {
                    $this->observations .= "\n";
                }
                $this->observations .= __('Ajuste de hora de salida para cumplir con el máximo de jornada (:minutes min)', ['minutes' => $maxMinutes]);
                break;

            case 'adjust_schedule':
                // For PAST events that exceed the daily limit:
                // Instead of truncating the event, mark it as exceptional so the admin can review it.
                // The event keeps its original start/end times on the correct date.
                $eventDate = Carbon::parse($this->start_date);
                if ($eventDate->isPast() || $eventDate->isToday()) {
                    // Mark as exceptional — keeps original times, just flags it for admin review
                    $this->isExceptionalOverride = true;
                    if (empty($this->observations)) $this->observations = '';
                    else $this->observations .= "\n";
                    $this->observations .= __('Evento excepcional: la duración supera el límite diario (:max min). Requiere revisión del administrador.', ['max' => $maxMinutes]);
                    // Do NOT modify start/end times — keep the original registration
                    break;
                }

                // For today's events (real-time clock-out scenario), align to schedule slots
                $user = Auth::user();
                $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
                $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];
                
                if (!empty($schedule)) {
                    // Get ALL slots (ignore day-of-week for manual adjustment)
                    // Sort by start time to use earliest slot
                    $slots = collect($schedule)->sortBy('start')->values();
                    
                    if ($slots->isNotEmpty()) {
                        $slot = $slots[0];
                        $slotStart = Carbon::parse($this->start_date . ' ' . $slot['start']); 
                        
                        // Set new start
                        $this->start_time = $slotStart->format('H:i:s');
                        
                        // Set new end = start + maxMinutes
                        $newEnd = $slotStart->copy()->addMinutes($maxMinutes);
                        $this->end_date = $newEnd->format('Y-m-d');
                        $this->end_time = $newEnd->format('H:i:s');
                        
                        if (empty($this->observations)) $this->observations = '';
                        else $this->observations .= "\n";
                        $this->observations .= __('Ajuste al horario laboral (:minutes min)', ['minutes' => $maxMinutes]);
                    } else {
                        // No slot today, fallback to adjust end
                        return $this->applyAdjustment('adjust_end');
                    }
                } else {
                     return $this->applyAdjustment('adjust_end');
                }
                break;
        }

        // Hide modal and try to save again
        $this->showAdjustmentModal = false;
        $this->save(); 
    }

    /**
     * Get all events for the calendar (same logic as Calendar::getEvents)
     */
    private function getAllEventsForCalendar()
    {
        $user = Auth::user();
        if (!$user || !$user->currentTeam) {
            return [];
        }

        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');

        // Get user events from current team
        $userEvents = \App\Models\Event::with('eventType')
            ->where('user_id', $user->id)
            ->where('team_id', $user->currentTeam->id)
            ->get()
            ->map(function ($eventModel) use ($teamTimezone, $user) {
                $iconHtml = $eventModel->is_open
                    ? '<i class="ml-1 mr-2 fa-solid fa-lock-open" style="color: #28a745;"></i>'
                    : '<i class="ml-1 mr-2 fa-solid fa-lock" style="color: #dc3545;"></i>';

                $eventColor = '#3788d8';
                
                if ($eventModel->is_exceptional) {
                    $eventColor = $user->currentTeam->special_event_color ?? '#DC2626';
                } elseif ($eventModel->eventType) {
                    if ($eventModel->eventType->color) {
                        $eventColor = $eventModel->eventType->color;
                    } elseif (!$eventModel->eventType->is_workday_type) {
                        $eventColor = $user->currentTeam->special_event_color ?? '#EA8000';
                    }
                } else {
                    $eventColor = $user->currentTeam->special_event_color ?? '#EA8000';
                }
                
                // Prepare end date for calendar display
                $endDate = null;
                if ($eventModel->end) {
                    if ($eventModel->eventType && $eventModel->eventType->is_all_day) {
                        $startDate = \Carbon\Carbon::parse($eventModel->start, 'UTC')->startOfDay();
                        $endDateCarbon = \Carbon\Carbon::parse($eventModel->end, 'UTC')->startOfDay();
                        
                        if (!$startDate->isSameDay($endDateCarbon)) {
                            $endDate = $endDateCarbon->addDay()->format('Y-m-d');
                        }
                    } else {
                        $endDate = \Carbon\Carbon::parse($eventModel->end, 'UTC')->setTimezone($teamTimezone)->toIso8601String();
                    }
                }
                
                return [
                    'id' => 'event_' . $eventModel->id,
                    'title' => $eventModel->description,
                    'iconHtml' => $iconHtml,
                    'start' => $eventModel->eventType && $eventModel->eventType->is_all_day
                        ? \Carbon\Carbon::parse($eventModel->start, 'UTC')->format('Y-m-d')
                        : \Carbon\Carbon::parse($eventModel->start, 'UTC')->setTimezone($teamTimezone)->toIso8601String(),
                    'end' => $endDate,
                    'color' => $eventColor,
                    'allDay' => $eventModel->eventType->is_all_day ?? false,
                    'editable' => $eventModel->is_open && ($user->ownsTeam($user->currentTeam) || $user->hasTeamRole($user->currentTeam, 'admin') || $eventModel->user_id === $user->id),
                ];
            });

        // Get team holidays
        $holidays = \App\Models\Holiday::where('team_id', $user->currentTeam->id)
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => 'holiday_' . $holiday->id,
                    'title' => $holiday->name,
                    'iconHtml' => '<i class="ml-1 mr-2 fa-solid fa-calendar-day" style="color: #ff6b35;"></i>',
                    'start' => $holiday->date->format('Y-m-d'),
                    'color' => '#ff6b35',
                    'allDay' => true,
                ];
            });

        return collect(array_merge($userEvents->all(), $holidays->all()));
    }

    /**
     * Get whether the selected event type is all-day.
     *
     * @return bool
     */
    public function getIsAllDayProperty(): bool
    {
        return $this->selectedEventType && $this->selectedEventType->is_all_day;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.events.add-event');
    }
}
