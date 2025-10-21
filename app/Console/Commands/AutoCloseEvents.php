<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCloseEvents extends Command
{
    protected $signature = 'events:autoclose';
    protected $description = 'Processes unconfirmed events based on team expiration settings.';

    public function handle()
    {
        Log::info('Starting AutoCloseEvents command...');

        $teams = Team::whereNotNull('event_expiration_days')->get();

        foreach ($teams as $team) {
            $expirationDays = $team->event_expiration_days;
            $expirationDate = Carbon::now()->subDays($expirationDays);

            Log::info("Processing team {$team->id} with expiration of {$expirationDays} days.");

            $expiredEvents = Event::where('is_confirmed', false)
                ->where('start', '<', $expirationDate)
                ->whereHas('user', function ($query) use ($team) {
                    $query->where('current_team_id', $team->id);
                })
                ->get();

            foreach ($expiredEvents as $event) {
                $this->processExpiredEvent($event);
            }
        }

        Log::info('AutoCloseEvents command finished.');
        $this->info('All applicable events have been processed.');
        return 0;
    }

    private function processExpiredEvent(Event $event)
    {
        Log::info("Processing expired event {$event->id} for user {$event->user_id}.");

        $updates = [
            'is_closed_automatically' => true,
            'observations' => ($event->observations ? $event->observations . ' ' : '') . __('Processed by automatic closure system.'),
        ];

        // Rule 1: Event is not confirmed and has no end time
        if (is_null($event->end)) {
            $updates['end'] = Carbon::parse($event->start)->addMinute();
            $updates['is_exceptional'] = true;
            Log::info("Event {$event->id}: No end time. Setting end time and marking as exceptional.");
        } else {
            // Rule 2 & 3: Event has start and end, but is not confirmed
            $isDiscrepant = $this->isWorkdayDurationDiscrepant($event);
            if ($isDiscrepant) {
                $updates['is_exceptional'] = true;
                Log::info("Event {$event->id}: Duration is discrepant. Marking as exceptional.");
            }
        }

        // Always confirm the event as it has been processed
        $updates['is_confirmed'] = true;

        $event->update($updates);
    }

    private function isWorkdayDurationDiscrepant(Event $event)
    {
        $user = $event->user;
        $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();
        $schedule = $workScheduleMeta ? json_decode($workScheduleMeta->meta_value, true) : [];

        if (empty($schedule)) {
            return false; // Cannot compare without a schedule
        }

        $eventStart = Carbon::parse($event->start);
        $dayOfWeek = $eventStart->format('N');
        $dayMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
        $dayAbbr = $dayMap[$dayOfWeek] ?? null;

        $todaysSlots = collect($schedule)->filter(fn($slot) => in_array($dayAbbr, $slot['days']));

        if ($todaysSlots->isEmpty()) {
            return false; // No slot for this day
        }

        $closestSlot = null;
        $minDiff = PHP_INT_MAX;

        foreach ($todaysSlots as $slot) {
            $slotStart = Carbon::parse($eventStart->format('Y-m-d') . ' ' . $slot['start']);
            $diff = $eventStart->diffInMinutes($slotStart, false);
            if (abs($diff) < $minDiff) {
                $minDiff = abs($diff);
                $closestSlot = $slot;
            }
        }

        if (!$closestSlot) {
            return false;
        }

        $slotStart = Carbon::parse($eventStart->format('Y-m-d') . ' ' . $closestSlot['start']);
        $slotEnd = Carbon::parse($eventStart->format('Y-m-d') . ' ' . $closestSlot['end']);
        $scheduledDuration = $slotEnd->diffInMinutes($slotStart);
        $eventDuration = Carbon::parse($event->end)->diffInMinutes($eventStart);

        if ($scheduledDuration == 0) return true; // Avoid division by zero, consider it discrepant.

        $differencePercentage = abs($eventDuration - $scheduledDuration) / $scheduledDuration;

        return $differencePercentage > 0.50;
    }
}
