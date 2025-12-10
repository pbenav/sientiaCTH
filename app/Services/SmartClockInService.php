<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\EventType;
use App\Models\ExceptionalClockInToken;
use App\Models\Message;
use App\Notifications\NewMessage;
use App\Traits\HandlesEventAuthorization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmartClockInService
{
    use HandlesEventAuthorization;

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
    public const STATUS_ERROR = 'ERROR';

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
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        $now = Carbon::now($teamTimezone);

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
                ->whereIn('name', ['Pausa', 'Pause'])
                ->where('is_break_type', true)
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
                'paused_at' => Carbon::parse($activePause->start, 'UTC')
                    ->setTimezone($teamTimezone)
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
                    'started_at' => Carbon::parse($openEvent->start, 'UTC')
                        ->setTimezone($teamTimezone)
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
                    'started_at' => Carbon::parse($openEvent->start, 'UTC')
                        ->setTimezone($teamTimezone)
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
    public function clockIn(User $user, int $eventTypeId, bool $overtime = false, string $source = null, $observations = null): array
    {
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        
        // Always start from UTC to avoid server timezone interference
        $nowUTC = Carbon::now('UTC');
        $nowTeamTz = $nowUTC->copy()->setTimezone($teamTimezone);

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
            
            $event = Event::create([
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
            ]);
            
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
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        
        // Always start from UTC to avoid server timezone interference
        $nowUTC = Carbon::now('UTC');
        $nowTeamTz = $nowUTC->copy()->setTimezone($teamTimezone);

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

            $event->update([
                'end' => $nowUTC->format('Y-m-d H:i:s'),
                'is_open' => false,
            ]);

            $startTime = Carbon::parse($event->start, 'UTC')
                ->setTimezone($teamTimezone)
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
     * Pause the current workday
     */
    public function pauseWorkday(User $user, int $pauseEventTypeId): array
    {
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        
        // Always start from UTC to avoid server timezone interference
        $nowUTC = Carbon::now('UTC');
        $nowTeamTz = $nowUTC->copy()->setTimezone($teamTimezone);

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
            if (!$pauseEventType || !$pauseEventType->is_break_type) {
                return [
                    'success' => false,
                    'status_code' => self::STATUS_ERROR,
                    'message' => __('Invalid pause event type')
                ];
            }

            // Create pause event
            $pauseEvent = Event::create([
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
                'is_extra_hours' => false, // Pause is not work time
                'is_closed_automatically' => false,
            ]);

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
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        
        // Always start from UTC to avoid server timezone interference
        $nowUTC = Carbon::now('UTC');
        $nowTeamTz = $nowUTC->copy()->setTimezone($teamTimezone);

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

            $pauseStartTime = Carbon::parse($pauseEvent->start, 'UTC')
                ->setTimezone($teamTimezone)
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
    public function requestExceptionalClockIn(User $user, int $eventTypeId): array
    {
        // Create exceptional clock-in token
        $token = $this->createExceptionalClockInToken($user);
        
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
    private function createExceptionalClockInToken(User $user): string
    {
        $team = $user->currentTeam;
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

}