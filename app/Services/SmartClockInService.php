<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\EventType;
use App\Models\ExceptionalClockInToken;
use App\Models\Message;
use App\Notifications\NewMessage;
use App\Traits\HandlesEventAuthorization;
use App\Traits\HandlesTimezoneConversion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmartClockInService
{
    use HandlesEventAuthorization;
    use HandlesTimezoneConversion;

    // Status Codes Constants
    public const STATUS_USER_OR_TEAM_NOT_FOUND = 'USER_OR_TEAM_NOT_FOUND';
    public const STATUS_NO_SCHEDULE = 'NO_SCHEDULE';
    public const STATUS_NO_WORKDAY_TYPE = 'NO_WORKDAY_TYPE';
    public const STATUS_RESUME_WORKDAY = 'RESUME_WORKDAY';
    public const STATUS_WORKING = 'WORKING';
    public const STATUS_CLOCK_OUT = 'CLOCK_OUT'; // Fallback for working without pause
    public const STATUS_OUTSIDE_SCHEDULE_CONFIRM = 'OUTSIDE_SCHEDULE_CONFIRM';
    public const STATUS_CAN_CLOCK_IN = 'CAN_CLOCK_IN';
    public const STATUS_CLOCK_IN_SUCCESS = 'CLOCK_IN_SUCCESS';
    public const STATUS_CLOCK_OUT_SUCCESS = 'CLOCK_OUT_SUCCESS';
    public const STATUS_PAUSE_SUCCESS = 'PAUSE_SUCCESS';
    public const STATUS_RESUME_SUCCESS = 'RESUME_SUCCESS';
    public const STATUS_EXCEPTIONAL_REQUEST_CREATED = 'EXCEPTIONAL_REQUEST_CREATED';
    public const STATUS_MAX_DURATION_EXCEEDED = 'MAX_DURATION_EXCEEDED';
    public const STATUS_ERROR = 'ERROR';

    /**
     * Check if an event exceeds the max workday duration for a user's team.
     * This method validates the TOTAL daily duration by summing all workday events from the same day.
     * 
     * @param User $user
     * @param Event $event The event being validated
     * @param Carbon|string|null $endTime If null, uses now (for open events)
     * @return array
     */
    public function validateMaxDuration(User $user, Event $event, $endTime = null): array
    {
        $team = $user->currentTeam;
        if (!$team->force_max_workday_duration || !$team->max_workday_duration_minutes) {
            return ['success' => true];
        }

        // 1. Calculate duration of current event
        $start = Carbon::parse($event->start, 'UTC');
        
        if ($endTime) {
            $end = $endTime instanceof Carbon ? $endTime->copy() : Carbon::parse($endTime, 'UTC');
        } else {
            $end = Carbon::now('UTC');
        }
        
        // Ensure end is in UTC for diff calculation
        $end = $end->setTimezone('UTC');
        
        $currentEventMinutes = $end->diffInMinutes($start);

        // 2. Get the event date in team timezone to determine which events belong to the "same day"
        $teamTimezone = $team->timezone ?? config('app.timezone');
        $eventDateStart = $start->copy()->setTimezone($teamTimezone)->startOfDay();
        $eventDateEnd = $eventDateStart->copy()->endOfDay();

        // Convert back to UTC for database query
        $dayStartUTC = $eventDateStart->copy()->setTimezone('UTC');
        $dayEndUTC = $eventDateEnd->copy()->setTimezone('UTC');

        // 3. Find all closed workday events from the same day (excluding current event)
        $query = Event::where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->when($event->exists, function($q) use ($event) {
                $q->where('id', '!=', $event->id);
            })
            ->whereHas('eventType', function($q) {
                $q->where('is_workday_type', true);
            })
            ->where('is_open', false) // Only closed events
            ->where('start', '>=', $dayStartUTC)
            ->where('start', '<=', $dayEndUTC);

        $dayEvents = $query->get();

        \Log::info('SmartClockInService: Validating daily duration', [
            'event_id' => $event->id,
            'day_start_utc' => $dayStartUTC->toDateTimeString(),
            'day_end_utc' => $dayEndUTC->toDateTimeString(),
            'found_prior_events_count' => $dayEvents->count(),
            'current_event_minutes' => $currentEventMinutes
        ]);

        // 4. Sum durations of all events from the day
        $totalDayMinutes = $currentEventMinutes;
        
        foreach ($dayEvents as $dayEvent) {
            if ($dayEvent->end) {
                $eventStart = Carbon::parse($dayEvent->start, 'UTC');
                $eventEnd = Carbon::parse($dayEvent->end, 'UTC');
                $minutes = $eventEnd->diffInMinutes($eventStart);
                $totalDayMinutes += $minutes;
                \Log::info('SmartClockInService: Adding event duration', ['event_id' => $dayEvent->id, 'minutes' => $minutes]);
            }
        }

        \Log::info('SmartClockInService: Total calculation', [
            'total_minutes' => $totalDayMinutes,
            'max_allowed' => $team->max_workday_duration_minutes
        ]);

        // 5. Validate against the limit
        if ($totalDayMinutes > $team->max_workday_duration_minutes) {
            return [
                'success' => false,
                'status_code' => self::STATUS_MAX_DURATION_EXCEEDED,
                'message' => __('Maximum daily workday duration exceeded (:minutes min). Total today: :current min.', [
                    'minutes' => $team->max_workday_duration_minutes,
                    'current' => $totalDayMinutes
                ]),
                'max_minutes' => $team->max_workday_duration_minutes,
                'current_minutes' => $totalDayMinutes,
                'event_id' => $event->id
            ];
        }

        return ['success' => true];
    }

    /**
     * Determine the action needed for smart clock-in/out
     * 
     * @param User|null $user
     * @return array
     */
    public function getClockAction(?User $user = null): array
    {
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user || !$user->currentTeam) {
            return [
                'can_clock' => false,
                'action' => null,
                'status_code' => self::STATUS_USER_OR_TEAM_NOT_FOUND,
                'message' => __('User or team not found'),
                'button_text' => __('Clock In/Out'),
                'button_class' => 'bg-gray-400 cursor-not-allowed'
            ];
        }

        // Get team timezone
        $teamTimezone = $this->getUserTimezone($user);
        $now = $this->nowInTeamTimezone($user->currentTeam);

        // Get work schedule
        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];

        if (empty($schedule)) {
            return [
                'can_clock' => false,
                'action' => 'redirect_to_profile',
                'status_code' => self::STATUS_NO_SCHEDULE,
                'message' => __('No work schedule configured. Configure your schedule to use smart clock-in.'),
                'button_text' => __('Configure Schedule'),
                'button_class' => 'bg-blue-600 hover:bg-blue-700 text-white',
                'redirect_url' => route('profile.show') . '?tab=preferences#work-schedule-section'
            ];
        }

        // Check if we're in a scheduled time slot
        $currentSlot = $this->getCurrentScheduledSlot($now, $schedule);
        
        // Get workday event type
        $workdayEventType = $user->currentTeam->eventTypes()
            ->where('is_workday_type', true)
            ->first();

        if (!$workdayEventType) {
            return [
                'can_clock' => false,
                'action' => null,
                'status_code' => self::STATUS_NO_WORKDAY_TYPE,
                'message' => __('No workday event type configured'),
                'button_text' => __('Clock In/Out'),
                'button_class' => 'bg-gray-400 cursor-not-allowed'
            ];
        }

        // Check for open events
        $openEvent = $this->getOpenEvent($user, $workdayEventType);
        
        // Check for active pause (safely handle if pause type doesn't exist)
        $pauseEventType = null;
        $activePause = null;
        
        try {
            $pauseEventType = $user->currentTeam->eventTypes()
                ->where('is_pause_type', true)
                ->first();
                
            if ($pauseEventType) {
                $activePause = $this->getOpenEvent($user, $pauseEventType);
            }
        } catch (\Exception $e) {
            // If pause detection fails, continue with normal flow
            $pauseEventType = null;
            $activePause = null;
        }

        if ($openEvent && $activePause) {
            // Resume from pause action
            return [
                'can_clock' => true,
                'action' => 'resume_workday',
                'status_code' => self::STATUS_RESUME_WORKDAY,
                'message' => __('Resume workday from pause'),
                'button_text' => __('Resume Work'),
                'button_class' => 'bg-blue-600 hover:bg-blue-700 text-white',
                'open_event_id' => $openEvent->id,
                'pause_event_id' => $activePause->id,
                'paused_at' => $this->utcToTeamTimezone($activePause->start, $teamTimezone)
                    ->format('H:i'),
                'current_slot' => $currentSlot
            ];
        } elseif ($openEvent && !$activePause) {
            // Working - show pause and clock out options if pause type exists, otherwise normal clock out
            if ($pauseEventType) {
                return [
                    'can_clock' => true,
                    'action' => 'working_options',
                    'status_code' => self::STATUS_WORKING,
                    'message' => __('Currently working'),
                    'button_text' => __('Working'),
                    'button_class' => 'bg-green-600 hover:bg-green-700 text-white',
                    'open_event_id' => $openEvent->id,
                    'started_at' => $this->utcToTeamTimezone($openEvent->start, $teamTimezone)
                        ->format('H:i'),
                    'current_slot' => $currentSlot,
                    'show_pause_option' => true,
                    'show_clock_out_option' => true,
                    'pause_event_type_id' => $pauseEventType->id
                ];
            } else {
                // Fallback to normal clock out behavior if no pause type
                return [
                    'can_clock' => true,
                    'action' => 'clock_out',
                    'status_code' => self::STATUS_CLOCK_OUT,
                    'message' => __('Clock out from work'),
                    'button_text' => __('Clock Out'),
                    'button_class' => 'bg-red-600 hover:bg-red-700 text-white',
                    'open_event_id' => $openEvent->id,
                    'started_at' => $this->utcToTeamTimezone($openEvent->start, $teamTimezone)
                        ->format('H:i'),
                    'current_slot' => $currentSlot
                ];
            }
        }

        // Check if force delay is enabled
        $forceDelay = $user->currentTeam->force_clock_in_delay ?? false;

        if (!$forceDelay) {
            // If force delay is disabled, ALWAYS allow clock-in as normal (never exceptional)
            // We only check schedule to show an informative warning message
            $isInSchedule = $this->isWithinWorkSchedule($now, $user);
            
            $message = $isInSchedule ? __('Clock in to work') : __('Clock in to work (outside schedule)');
            $buttonClass = $isInSchedule ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-orange-600 hover:bg-orange-700 text-white';

            return [
                'can_clock' => true,
                'action' => 'clock_in',
                'status_code' => self::STATUS_CAN_CLOCK_IN,
                'message' => $message,
                'button_text' => __('Clock In'),
                'button_class' => $buttonClass,
                'current_slot' => $currentSlot,
                'event_type_id' => $workdayEventType->id,
                'overtime' => false
            ];
        }

        // If force delay is enabled, check strict entry window
        $isAllowed = $this->isWithinEntryWindow($now, $user);
        
        if (!$isAllowed) {
            return [
                'can_clock' => false,
                'action' => 'confirm_exceptional_clock_in',
                'status_code' => self::STATUS_OUTSIDE_SCHEDULE_CONFIRM,
                'message' => __('You are outside your work schedule. Do you want to make an exceptional clock-in?'),
                'button_text' => __('Exceptional Clock In'),
                'button_class' => 'bg-yellow-600 hover:bg-yellow-700 text-white',
                'outside_schedule' => true,
                'next_slot' => $this->getNextScheduledSlot($now, $schedule),
                'event_type_id' => $workdayEventType->id
            ];
        }

        // Allowed and within strict window
        return [
            'can_clock' => true,
            'action' => 'clock_in',
            'status_code' => self::STATUS_CAN_CLOCK_IN,
            'message' => __('Clock in to work'),
            'button_text' => __('Clock In'),
            'button_class' => 'bg-green-600 hover:bg-green-700 text-white',
            'current_slot' => $currentSlot,
            'event_type_id' => $workdayEventType->id,
            'overtime' => false
        ];
    }

    /**
     * Execute the clock in action
     */
    public function clockIn(User $user, int $eventTypeId, bool $overtime = false, string $source = null, $observations = null, array $location = null): array
    {
        $teamTimezone = $this->getUserTimezone($user);
        
        // Always start from UTC to avoid server timezone interference
        $nowUTC = Carbon::now('UTC');
        $nowTeamTz = $this->utcToTeamTimezone($nowUTC->toDateTimeString(), $teamTimezone);

        Log::debug('[SmartClockInService][clockIn] Timezone conversion:', [
            'team_timezone' => $teamTimezone,
            'now_utc' => $nowUTC->toDateTimeString() . ' UTC',
            'now_in_team_tz' => $nowTeamTz->toDateTimeString() . ' ' . $teamTimezone,
            'timestamp' => $nowUTC->timestamp,
        ]);

        try {
            // Get event type to use its name as default description
            $eventType = EventType::find($eventTypeId);
            
            // Si se pasa observations desde el API, usarlo. Si no, usar el valor por defecto para excepcionales.
            if (!$observations && $overtime && $source === 'mobile_api') {
                $observations = __('Exceptional clock-in made through mobile API (outside work schedule)');
            }
            
            $eventData = [
                'user_id' => $user->id,
                'event_type_id' => $eventTypeId,
                'team_id' => $user->current_team_id,
                'work_center_id' => $user->currentTeam->workCenters()->first()?->id,
                'start' => $nowUTC->format('Y-m-d H:i:s'),
                'end' => null,
                'description' => $eventType ? $eventType->name : __('Workday'),
                'observations' => $observations,
                'is_open' => true,
                'is_authorized' => false,
                'is_exceptional' => $overtime,
                'is_extra_hours' => $eventType ? !$eventType->is_workday_type : false,
                'is_closed_automatically' => false,
            ];
            
            // Añadir geolocalización si el usuario la tiene habilitada y se proporcionó
            if ($user->geolocation_enabled && $location && isset($location['latitude']) && isset($location['longitude'])) {
                $eventData['latitude'] = $location['latitude'];
                $eventData['longitude'] = $location['longitude'];
            }
            
            // Añadir dirección IP
            $eventData['ip_address'] = request()->ip();
            
            $event = Event::create($eventData);
            
            Log::debug('[SmartClockInService][clockIn] Event created:', [
                'event_id' => $event->id,
                'start_in_db' => $event->start,
            ]);

            $message = $overtime 
                ? __('Clocked in successfully at :time (outside schedule)', ['time' => $nowTeamTz->format('H:i')])
                : __('Clocked in successfully at :time', ['time' => $nowTeamTz->format('H:i')]);

            return [
                'success' => true,
                'status_code' => self::STATUS_CLOCK_IN_SUCCESS,
                'message' => $message,
                'event_id' => $event->id,
                'overtime' => $overtime
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => self::STATUS_ERROR,
                'message' => __('Error clocking in: :error', ['error' => $e->getMessage()])
            ];
        }
    }

    /**
     * Execute the clock out action
     */
    public function clockOut(User $user, int $eventId): array
    {
        $teamTimezone = $this->getUserTimezone($user);
        
        // Always start from UTC to avoid server timezone interference
        $nowUTC = Carbon::now('UTC');
        $nowTeamTz = $this->utcToTeamTimezone($nowUTC->toDateTimeString(), $teamTimezone);

        try {
            $event = Event::where('id', $eventId)
                ->where('user_id', $user->id)
                ->where('is_open', true)
                ->first();

            if (!$event) {
                return [
                    'success' => false,
                    'status_code' => self::STATUS_ERROR,
                    'message' => __('Event not found or already closed')
                ];
            }

            // Check for max workday duration limit using centralized validation
            $validation = $this->validateMaxDuration($user, $event, Carbon::now('UTC'));
            
            if (!$validation['success']) {
                return $validation;
            }

            $event->update([
                'end' => $nowUTC->format('Y-m-d H:i:s'),
                'is_open' => false,
            ]);

            $startTime = $this->utcToTeamTimezone($event->start, $teamTimezone)
                ->format('H:i');

            return [
                'success' => true,
                'status_code' => self::STATUS_CLOCK_OUT_SUCCESS,
                'message' => __('Clocked out successfully. Worked from :start to :end', [
                    'start' => $startTime,
                    'end' => $nowTeamTz->format('H:i')
                ]),
                'event_id' => $event->id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => self::STATUS_ERROR,
                'message' => __('Error clocking out: :error', ['error' => $e->getMessage()])
            ];
        }
    }

    /**
     * Execute the clock out action with adjustment
     */
    public function clockOutWithAdjustment(User $user, int $eventId, string $adjustmentType): array
    {
        try {
            $event = Event::where('id', $eventId)
                ->where('user_id', $user->id)
                ->where('is_open', true)
                ->first();

            if (!$event) {
                return [
                    'success' => false,
                    'status_code' => self::STATUS_ERROR,
                    'message' => __('Event not found or already closed')
                ];
            }

            $team = $user->currentTeam;
            $maxMinutes = $team->max_workday_duration_minutes;
            
            $start = Carbon::parse($event->start);
            $nowUTC = Carbon::now('UTC');
            
            // For safety, if someone calls this without a limit set, use actual duration but this shouldn't happen
            if (!$maxMinutes) {
                return $this->clockOut($user, $eventId);
            }

            switch ($adjustmentType) {
                case 'adjust_start':
                    // Move start forward so start -> now = maxMinutes
                    $newStart = $nowUTC->copy()->subMinutes($maxMinutes);
                    $event->update([
                        'start' => $newStart->format('Y-m-d H:i:s'),
                        'end' => $nowUTC->format('Y-m-d H:i:s'),
                        'is_open' => false,
                        'observations' => ($event->observations ? $event->observations . "\n" : "") . __('Ajuste de hora de inicio para cumplir con el máximo de jornada (:minutes min)', ['minutes' => $maxMinutes])
                    ]);
                    break;

                case 'adjust_end':
                    // Move end backward so start -> newEnd = maxMinutes
                    $newEnd = $start->copy()->addMinutes($maxMinutes);
                    $event->update([
                        'end' => $newEnd->format('Y-m-d H:i:s'),
                        'is_open' => false,
                        'observations' => ($event->observations ? $event->observations . "\n" : "") . __('Ajuste de hora de salida para cumplir con el máximo de jornada (:minutes min)', ['minutes' => $maxMinutes])
                    ]);
                    break;

                case 'adjust_schedule':
                    return $this->adjustEventToScheduleProportional($user, $event, $maxMinutes);

                default:
                    return [
                        'success' => false,
                        'status_code' => self::STATUS_ERROR,
                        'message' => __('Invalid adjustment type')
                    ];
            }

            return [
                'success' => true,
                'status_code' => self::STATUS_CLOCK_OUT_SUCCESS,
                'message' => __('Clock out successful with adjustment'),
                'event_id' => $event->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => self::STATUS_ERROR,
                'message' => __('Error adjusting clock out: :error', ['error' => $e->getMessage()])
            ];
        }
    }

    /**
     * Adjust event to schedule proportional - distributes time across multiple slots
     */
    private function adjustEventToScheduleProportional(User $user, Event $event, int $maxMinutes): array
    {
        // Get work schedule
        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];

        if (empty($schedule)) {
            // Fallback to adjust_end if no schedule
            return $this->clockOutWithAdjustment($user, $event->id, 'adjust_end');
        }

        $nowUTC = Carbon::now('UTC');
        $teamTimezone = $this->getUserTimezone($user);
        $nowLocal = $this->utcToTeamTimezone($nowUTC->toDateTimeString(), $teamTimezone);
        
        // Calculate time already used today by other events
        $team = $user->currentTeam;
        $eventDate = $nowLocal->copy()->startOfDay();
        $dayStartUTC = $eventDate->copy()->setTimezone('UTC');
        $dayEndUTC = $eventDate->copy()->endOfDay()->setTimezone('UTC');
        
        // Get all other workday events from the same day (excluding current event)
        $dayEvents = Event::where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->where('id', '!=', $event->id)
            ->whereHas('eventType', function($q) {
                $q->where('is_workday_type', true);
            })
            ->where('is_open', false)
            ->where('start', '>=', $dayStartUTC)
            ->where('start', '<=', $dayEndUTC)
            ->get();
        
        // Calculate total minutes already used today
        $usedMinutes = 0;
        foreach ($dayEvents as $dayEvent) {
            if ($dayEvent->end) {
                $eventStart = Carbon::parse($dayEvent->start, 'UTC');
                $eventEnd = Carbon::parse($dayEvent->end, 'UTC');
                $usedMinutes += $eventEnd->diffInMinutes($eventStart);
            }
        }
        
        // Calculate available minutes (max duration - already used)
        $maxDuration = $team->max_workday_duration_minutes ?? 480; // Default 8 hours
        $availableMinutes = max(0, $maxDuration - $usedMinutes);
        
        // Limit to available minutes
        $remainingMinutes = min($maxMinutes, $availableMinutes);
        
        if ($remainingMinutes <= 0) {
            return [
                'success' => false,
                'status_code' => self::STATUS_ERROR,
                'message' => __('No hay tiempo disponible. Ya se ha alcanzado el máximo de jornada diaria.')
            ];
        }
        
        // Get ALL slots (ignore day-of-week for clock-out adjustment)
        // Sort by start time to distribute chronologically
        $allSlots = $schedule;
        usort($allSlots, function($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        if (empty($allSlots)) {
            return $this->clockOutWithAdjustment($user, $event->id, 'adjust_end');
        }

        // Calculate how much time we need to distribute
        $eventsToCreate = [];
        
        foreach ($allSlots as $slot) {
            if ($remainingMinutes <= 0) break;
            
            $slotStart = Carbon::parse($nowLocal->format('Y-m-d') . ' ' . $slot['start'], $teamTimezone);
            $slotEnd = Carbon::parse($nowLocal->format('Y-m-d') . ' ' . $slot['end'], $teamTimezone);
            
            // Handle slots that cross midnight
            if ($slotEnd->lt($slotStart)) {
                $slotEnd->addDay();
            }
            
            $slotDurationMinutes = $slotEnd->diffInMinutes($slotStart);
            
            // Determine how much of this slot to fill
            $minutesToUse = min($remainingMinutes, $slotDurationMinutes);
            
            // Calculate actual end time for this slot
            $actualEnd = $slotStart->copy()->addMinutes($minutesToUse);
            
            $eventsToCreate[] = [
                'start' => $slotStart,
                'end' => $actualEnd,
                'minutes' => $minutesToUse
            ];
            
            $remainingMinutes -= $minutesToUse;
        }

        if (empty($eventsToCreate)) {
            return $this->clockOutWithAdjustment($user, $event->id, 'adjust_end');
        }

        // Update the first event (the current one)
        $firstEvent = $eventsToCreate[0];
        $event->update([
            'start' => Carbon::parse($firstEvent['start']->toDateTimeString(), $teamTimezone)->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'end' => Carbon::parse($firstEvent['end']->toDateTimeString(), $teamTimezone)->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'is_open' => true,
            'observations' => ($event->observations ? $event->observations . "\n" : "") . 
                __('Ajuste automático al primer tramo horario (:minutes min)', ['minutes' => $firstEvent['minutes']])
        ]);

        // Create additional events for remaining slots
        for ($i = 1; $i < count($eventsToCreate); $i++) {
            $slotEvent = $eventsToCreate[$i];
            
            Event::create([
                'user_id' => $user->id,
                'event_type_id' => $event->event_type_id,
                'team_id' => $user->current_team_id,
                'work_center_id' => $event->work_center_id,
                'start' => Carbon::parse($slotEvent['start']->toDateTimeString(), $teamTimezone)->setTimezone('UTC')->format('Y-m-d H:i:s'),
                'end' => Carbon::parse($slotEvent['end']->toDateTimeString(), $teamTimezone)->setTimezone('UTC')->format('Y-m-d H:i:s'),
                'description' => $event->description,
                'observations' => __('Ajuste automático al tramo horario :number (:minutes min)', [
                    'number' => $i + 1,
                    'minutes' => $slotEvent['minutes']
                ]),
                'is_open' => true,
                'is_authorized' => false,
                'is_exceptional' => false,
                'is_extra_hours' => false,
                'is_closed_automatically' => false,
                'ip_address' => request()->ip(),
            ]);
        }

        $totalEvents = count($eventsToCreate);
        $totalMinutes = $maxMinutes - $remainingMinutes;

        return [
            'success' => true,
            'status_code' => self::STATUS_CLOCK_OUT_SUCCESS,
            'message' => __('Tiempo distribuido en :count tramos (:minutes min total)', [
                'count' => $totalEvents,
                'minutes' => $totalMinutes
            ]),
            'event_id' => $event->id,
            'additional_events' => $totalEvents - 1
        ];
    }

    /**
     * Pause the current workday
     */
    public function pauseWorkday(User $user, int $pauseEventTypeId, array $location = null): array
    {
        $teamTimezone = $this->getUserTimezone($user);
        
        // Always start from UTC to avoid server timezone interference
        $nowUTC = Carbon::now('UTC');
        $nowTeamTz = $this->utcToTeamTimezone($nowUTC->toDateTimeString(), $teamTimezone);

        try {
            // Verify there's an active workday event
            $workdayEventType = $user->currentTeam->eventTypes()
                ->where('is_workday_type', true)
                ->first();
                
            if (!$workdayEventType) {
                return [
                    'success' => false,
                    'status_code' => self::STATUS_NO_WORKDAY_TYPE,
                    'message' => __('No workday event type configured')
                ];
            }

            $openWorkdayEvent = $this->getOpenEvent($user, $workdayEventType);
            if (!$openWorkdayEvent) {
                return [
                    'success' => false,
                    'status_code' => self::STATUS_ERROR,
                    'message' => __('No active workday to pause')
                ];
            }

            // Get pause event type
            $pauseEventType = EventType::find($pauseEventTypeId);
            if (!$pauseEventType || !$pauseEventType->is_pause_type) {
                return [
                    'success' => false,
                    'status_code' => self::STATUS_ERROR,
                    'message' => __('Invalid pause event type')
                ];
            }

            // Create pause event
            $pauseEventData = [
                'user_id' => $user->id,
                'event_type_id' => $pauseEventTypeId,
                'team_id' => $user->current_team_id,
                'work_center_id' => $user->currentTeam->workCenters()->first()?->id,
                'start' => $nowUTC->format('Y-m-d H:i:s'),
                'end' => null,
                'description' => $pauseEventType->name,
                'is_open' => true,
                'is_authorized' => false,
                'is_exceptional' => false,
                'is_extra_hours' => true,
                'is_closed_automatically' => false,
                'ip_address' => request()->ip(),
            ];
            
            // Añadir geolocalización si el usuario la tiene habilitada y se proporcionó
            if ($user->geolocation_enabled && $location && isset($location['latitude']) && isset($location['longitude'])) {
                $pauseEventData['latitude'] = $location['latitude'];
                $pauseEventData['longitude'] = $location['longitude'];
            }
            
            $pauseEvent = Event::create($pauseEventData);

            return [
                'success' => true,
                'status_code' => self::STATUS_PAUSE_SUCCESS,
                'message' => __('Workday paused at :time', ['time' => $nowTeamTz->format('H:i')]),
                'pause_event_id' => $pauseEvent->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => self::STATUS_ERROR,
                'message' => __('Error pausing workday: :error', ['error' => $e->getMessage()])
            ];
        }
    }

    /**
     * Resume workday from pause
     */
    public function resumeWorkday(User $user, int $pauseEventId): array
    {
        $teamTimezone = $this->getUserTimezone($user);
        
        // Always start from UTC to avoid server timezone interference
        $nowUTC = Carbon::now('UTC');
        $nowTeamTz = $this->utcToTeamTimezone($nowUTC->toDateTimeString(), $teamTimezone);

        try {
            // Find and close the pause event
            $pauseEvent = Event::where('id', $pauseEventId)
                ->where('user_id', $user->id)
                ->where('is_open', true)
                ->first();

            if (!$pauseEvent) {
                return [
                    'success' => false,
                    'status_code' => self::STATUS_ERROR,
                    'message' => __('Pause event not found or already closed')
                ];
            }

            // Close the pause event
            $pauseEvent->update([
                'end' => $nowUTC->format('Y-m-d H:i:s'),
                'is_open' => false,
            ]);

            $pauseStartTime = $this->utcToTeamTimezone($pauseEvent->start, $teamTimezone)
                ->format('H:i');

            return [
                'success' => true,
                'status_code' => self::STATUS_RESUME_SUCCESS,
                'message' => __('Workday resumed at :time (paused from :start)', [
                    'time' => $nowTeamTz->format('H:i'),
                    'start' => $pauseStartTime
                ]),
                'pause_event_id' => $pauseEvent->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => self::STATUS_ERROR,
                'message' => __('Error resuming workday: :error', ['error' => $e->getMessage()])
            ];
        }
    }

    /**
     * Handle exceptional clock-in request when user is outside schedule
     */
    public function requestExceptionalClockIn(User $user, int $eventTypeId, array $location = null): array
    {
        // Create exceptional clock-in token
        $token = $this->createExceptionalClockInToken($user, $location);
        
        return [
            'success' => true,
            'action' => 'redirect_to_exceptional_clock_in',
            'status_code' => self::STATUS_EXCEPTIONAL_REQUEST_CREATED,
            'message' => __('Exceptional clock-in request created. Please complete the process.'),
            'redirect_url' => route('exceptional.clock-in.form', ['token' => $token])
        ];
    }

    /**
     * Create exceptional clock-in token and send message to admin
     */
    private function createExceptionalClockInToken(User $user, array $location = null): string
    {
        $team = $user->currentTeam;
        $token = Str::random(60);
        
        ExceptionalClockInToken::create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'token' => $token,
            'expires_at' => now()->addMinutes($team->clock_in_grace_period_minutes ?? 10),
            'location_data' => $location, // Save location for later use when approving/creating event
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
        
        return $token;
    }

    /**
     * Check if current time is within a scheduled slot
     */
    public function getCurrentScheduledSlot(Carbon $now, array $schedule): ?array
    {
        $dayIso = (int) $now->format('N'); // Número ISO: 1=Lunes, 7=Domingo

        foreach ($schedule as $slot) {
            // Comprobar usando solo números ISO (post-migración)
            if (!in_array($dayIso, $slot['days']) && !in_array((string)$dayIso, $slot['days'])) {
                continue;
            }

            $slotStart = Carbon::parse($now->format('Y-m-d') . ' ' . $slot['start'], $now->getTimezone());
            $slotEnd = Carbon::parse($now->format('Y-m-d') . ' ' . $slot['end'], $now->getTimezone());

            if ($now->between($slotStart, $slotEnd)) {
                return array_merge($slot, [
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd
                ]);
            }
        }

        return null;
    }

    /**
     * Check if current time is near (within 15 minutes) of a scheduled slot
     */
    private function isNearScheduledTime(Carbon $now, array $schedule): bool
    {
        $dayIso = (int) $now->format('N'); // Número ISO: 1=Lunes, 7=Domingo
        $tolerance = 15; // minutes

        foreach ($schedule as $slot) {
            if (!in_array($dayIso, $slot['days']) && !in_array((string)$dayIso, $slot['days'])) {
                continue;
            }

            $slotStart = Carbon::parse($now->format('Y-m-d') . ' ' . $slot['start'], $now->getTimezone());
            $slotEnd = Carbon::parse($now->format('Y-m-d') . ' ' . $slot['end'], $now->getTimezone());

            // Check if within tolerance of start or end time
            if ($now->between($slotStart->copy()->subMinutes($tolerance), $slotEnd->copy()->addMinutes($tolerance))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get next scheduled slot
     */
    public function getNextScheduledSlot(Carbon $now, array $schedule): ?array
    {
        $dayIso = (int) $now->format('N'); // Número ISO: 1=Lunes, 7=Domingo
        $nextSlot = null;
        $minDiff = null;

        foreach ($schedule as $slot) {
            if (!in_array($dayIso, $slot['days']) && !in_array((string)$dayIso, $slot['days'])) {
                continue;
            }

            $slotStart = Carbon::parse($now->format('Y-m-d') . ' ' . $slot['start'], $now->getTimezone());
            
            if ($slotStart->gt($now)) {
                $diff = $now->diffInMinutes($slotStart);
                
                if ($minDiff === null || $diff < $minDiff) {
                    $minDiff = $diff;
                    $nextSlot = array_merge($slot, [
                        'start_time' => $slotStart,
                        'minutes_until' => $diff
                    ]);
                }
            }
        }

        return $nextSlot;
    }

    /**
     * Get open event for user
     */
    private function getOpenEvent(User $user, EventType $workdayEventType): ?Event
    {
        return Event::where('user_id', $user->id)
            ->where('event_type_id', $workdayEventType->id)
            ->where('is_open', true)
            ->whereNull('end')
            ->orderBy('start', 'desc')
            ->first();
    }

    /**
     * Get day ISO number (kept for backward compatibility but deprecated)
     * Use ISO numbers directly instead.
     * 
     * @deprecated This method is no longer needed after migration to ISO numbers
     * @param int $dayOfWeek ISO day number (1=Monday, 7=Sunday)
     * @return int
     */
    private function getDayInitial(int $dayOfWeek): int
    {
        // Devolver directamente el número ISO
        return $dayOfWeek;
    }

    /**
     * Backward compatibility wrapper for isWithinWorkSchedule
     * @deprecated Use isWithinWorkSchedule instead
     */
    public function isUserWithinWorkSchedule($timeToCheck, $user = null)
    {
        return $this->isWithinWorkSchedule($timeToCheck, $user);
    }

}