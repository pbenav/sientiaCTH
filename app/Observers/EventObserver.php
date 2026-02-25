<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\User;
use App\Models\EventType;
use App\Services\SmartClockInService;
use App\Exceptions\MaxWorkdayDurationExceededException;
use Carbon\Carbon;

class EventObserver
{
    /**
     * Handle the Event "saving" event.
     *
     * @param  \App\Models\Event  $event
     * @return void
     * @throws \App\Exceptions\MaxWorkdayDurationExceededException
     */
    public function saving(Event $event)
    {

        // Skip validation if the event is being force-closed/adjusted systematically
        // or if it's not a workday type or doesn't have an end time yet (is_open)
        // Note: SmartClockInService handles is_open logic, but we need to be careful not to block clock-ins.
        
        // Only validate if we have a user and it's a workday event
        $user = $event->user;
        if (!$user && $event->user_id) {
            $user = User::find($event->user_id);
        }

        $eventType = $event->eventType;
        if (!$eventType && $event->event_type_id) {
            $eventType = EventType::find($event->event_type_id);
        }

        if (!$user || !$eventType || !$eventType->is_workday_type || $event->is_exceptional) {
            return;
        }

        // If 'end' is dirty or 'start' is dirty, check duration if 'end' is present.
        if ($event->end) {
            $service = app(SmartClockInService::class);
            $validation = $service->validateMaxDuration($user, $event, $event->end);

            if (!$validation['success'] && 
                isset($validation['status_code']) && 
                $validation['status_code'] === SmartClockInService::STATUS_MAX_DURATION_EXCEEDED) {
                
                throw new MaxWorkdayDurationExceededException(
                    $validation['max_minutes'], 
                    $validation['current_minutes'], 
                    $event
                );
            }
        }
    }
}
