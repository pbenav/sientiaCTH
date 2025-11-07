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
use Illuminate\Support\Str;

class SmartClockInService
{
    use HandlesEventAuthorization;

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
                ->where('name', 'Pausa')
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

        // Check if user is outside work schedule
        $isOutsideSchedule = !$this->isUserWithinWorkSchedule($user, $now);
        
        // If outside schedule and force_clock_in_delay is enabled, require exceptional clock-in
        if ($isOutsideSchedule && $user->currentTeam->force_clock_in_delay) {
            return [
                'can_clock' => false,
                'action' => 'confirm_exceptional_clock_in',
                'message' => __('You are outside your work schedule. Do you want to make an exceptional clock-in?'),
                'button_text' => __('Exceptional Clock In'),
                'button_class' => 'bg-yellow-600 hover:bg-yellow-700 text-white',
                'outside_schedule' => true,
                'next_slot' => $this->getNextScheduledSlot($now, $schedule),
                'event_type_id' => $workdayEventType->id
            ];
        }

        // Clock in action - Allow clocking in if within schedule or force delay is disabled
        $message = __('Clock in to work');
        $buttonClass = 'bg-green-600 hover:bg-green-700 text-white';
        
        // If outside schedule but force_clock_in_delay is disabled, show warning but allow clock-in
        if ($isOutsideSchedule && !$user->currentTeam->force_clock_in_delay) {
            $message = __('Clock in to work (outside schedule)');
            $buttonClass = 'bg-orange-600 hover:bg-orange-700 text-white';
        }
        
        return [
            'can_clock' => true,
            'action' => 'clock_in',
            'message' => $message,
            'button_text' => __('Clock In'),
            'button_class' => $buttonClass,
            'current_slot' => $currentSlot,
            'event_type_id' => $workdayEventType->id,
            'overtime' => !$currentSlot
        ];
    }

    /**
     * Execute the clock in action
     */
    public function clockIn(User $user, int $eventTypeId, bool $overtime = false, string $source = null): array
    {
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        $now = Carbon::now($teamTimezone);

        // Convert to UTC for storage
        $nowUTC = $now->copy()->setTimezone('UTC');

        try {
            // Get event type to use its name as default description
            $eventType = EventType::find($eventTypeId);
            
            // Prepare observations for exceptional events from mobile API
            $observations = null;
            if ($overtime && $source === 'mobile_api') {
                $observations = 'Exceptional clock-in made through mobile API (outside work schedule)';
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

            $message = $overtime 
                ? __('Clocked in successfully at :time (outside schedule)', ['time' => $now->format('H:i')])
                : __('Clocked in successfully at :time', ['time' => $now->format('H:i')]);

            return [
                'success' => true,
                'message' => $message,
                'event_id' => $event->id,
                'overtime' => $overtime
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
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
        $now = Carbon::now($teamTimezone);

        // Convert to UTC for storage
        $nowUTC = $now->copy()->setTimezone('UTC');

        try {
            $event = Event::where('id', $eventId)
                ->where('user_id', $user->id)
                ->where('is_open', true)
                ->first();

            if (!$event) {
                return [
                    'success' => false,
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
                'message' => __('Clocked out successfully. Worked from :start to :end', [
                    'start' => $startTime,
                    'end' => $now->format('H:i')
                ]),
                'event_id' => $event->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
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
        $now = Carbon::now($teamTimezone);
        $nowUTC = $now->copy()->setTimezone('UTC');

        try {
            // Verify there's an active workday event
            $workdayEventType = $user->currentTeam->eventTypes()
                ->where('is_workday_type', true)
                ->first();
                
            if (!$workdayEventType) {
                return [
                    'success' => false,
                    'message' => __('No workday event type configured')
                ];
            }

            $openWorkdayEvent = $this->getOpenEvent($user, $workdayEventType);
            if (!$openWorkdayEvent) {
                return [
                    'success' => false,
                    'message' => __('No active workday to pause')
                ];
            }

            // Get pause event type
            $pauseEventType = EventType::find($pauseEventTypeId);
            if (!$pauseEventType || !$pauseEventType->is_break_type) {
                return [
                    'success' => false,
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
                'message' => __('Workday paused at :time', ['time' => $now->format('H:i')]),
                'pause_event_id' => $pauseEvent->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
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
        $now = Carbon::now($teamTimezone);
        $nowUTC = $now->copy()->setTimezone('UTC');

        try {
            // Find and close the pause event
            $pauseEvent = Event::where('id', $pauseEventId)
                ->where('user_id', $user->id)
                ->where('is_open', true)
                ->first();

            if (!$pauseEvent) {
                return [
                    'success' => false,
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
                'message' => __('Workday resumed at :time (paused from :start)', [
                    'time' => $now->format('H:i'),
                    'start' => $pauseStartTime
                ]),
                'pause_event_id' => $pauseEvent->id
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
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
        $this->createExceptionalClockInToken($user);
        
        return [
            'success' => true,
            'action' => 'redirect_to_exceptional_clock_in',
            'message' => __('Exceptional clock-in request created. Please complete the process.'),
            'redirect_url' => route('events')
        ];
    }

    /**
     * Create exceptional clock-in token and send message to admin
     */
    private function createExceptionalClockInToken(User $user): void
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
    }

    /**
     * Check if current time is within a scheduled slot
     */
    private function getCurrentScheduledSlot(Carbon $now, array $schedule): ?array
    {
        $dayInitial = $this->getDayInitial($now->format('N'));

        foreach ($schedule as $slot) {
            if (!in_array($dayInitial, $slot['days'])) {
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
        $dayInitial = $this->getDayInitial($now->format('N'));
        $tolerance = 15; // minutes

        foreach ($schedule as $slot) {
            if (!in_array($dayInitial, $slot['days'])) {
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
    private function getNextScheduledSlot(Carbon $now, array $schedule): ?array
    {
        $dayInitial = $this->getDayInitial($now->format('N'));
        $nextSlot = null;
        $minDiff = null;

        foreach ($schedule as $slot) {
            if (!in_array($dayInitial, $slot['days'])) {
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
     * Get day initial from day number
     */
    private function getDayInitial(int $dayOfWeek): string
    {
        $days = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
        return $days[$dayOfWeek - 1];
    }

    /**
     * Check if a specific user is within their work schedule
     */
    public function isUserWithinWorkSchedule(User $user, Carbon $timeToCheck): bool
    {
        if (!$user || !$user->currentTeam) {
            return false;
        }

        $team = $user->currentTeam;
        $workScheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();

        if (!$workScheduleMeta || !$team || empty(json_decode($workScheduleMeta->meta_value, true))) {
            return true; // If no schedule configured, allow clock-in
        }

        $workSchedule = json_decode($workScheduleMeta->meta_value, true);
        $delayMinutes = $team->clock_in_delay_minutes ?? 0;

        $dayOfWeek = $timeToCheck->format('N');
        $currentDayLetter = $this->getDayInitial($dayOfWeek);

        $isWithinAnySlot = false;
        foreach ($workSchedule as $slot) {
            if (isset($slot['days']) && in_array($currentDayLetter, $slot['days']) && isset($slot['start']) && isset($slot['end'])) {
                $startTime = Carbon::parse($timeToCheck->format('Y-m-d') . ' ' . $slot['start']);
                $endTime = Carbon::parse($timeToCheck->format('Y-m-d') . ' ' . $slot['end']);

                if ($endTime->lessThan($startTime)) {
                    $endTime->addDay();
                }

                $startTimeWithGrace = $startTime->copy()->subMinutes($delayMinutes);
                $endTimeWithGrace = $endTime->copy()->addMinutes($delayMinutes);

                if ($timeToCheck->between($startTimeWithGrace, $endTimeWithGrace)) {
                    $isWithinAnySlot = true;
                    break;
                }
            }
        }

        if (!$team->force_clock_in_delay) {
            return $isWithinAnySlot;
        }

        return $isWithinAnySlot;
    }
}