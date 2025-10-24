<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCloseEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:autoclose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically closes open events that have passed their scheduled end time.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Starting AutoCloseEvents command...');

        $openEvents = Event::where('is_open', true)->get();

        foreach ($openEvents as $event) {
            $user = $event->user;
            $workScheduleMeta = $user->meta()->where('meta_key', 'work_schedule')->first();
            $schedule = $workScheduleMeta ? json_decode($workScheduleMeta->meta_value, true) : [];

            if (empty($schedule)) {
                Log::info("User {$user->id} has no work schedule. Skipping event {$event->id}.");
                continue;
            }

            $eventStart = Carbon::parse($event->start);
            $dayOfWeek = $eventStart->format('N');
            $dayMap = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
            $dayAbbr = $dayMap[$dayOfWeek] ?? null;

            $todaysSlots = collect($schedule)->filter(function ($slot) use ($dayAbbr) {
                return in_array($dayAbbr, $slot['days']);
            });

            if ($todaysSlots->isEmpty()) {
                 Log::info("No schedule found for user {$user->id} on day {$dayAbbr}. Skipping event {$event->id}.");
                continue;
            }

            // Find the correct slot for the event's start time
            $correctSlot = null;
            foreach ($todaysSlots as $slot) {
                $slotStart = Carbon::parse($eventStart->format('Y-m-d') . ' ' . $slot['start']);
                $slotEnd = Carbon::parse($eventStart->format('Y-m-d') . ' ' . $slot['end']);

                // Handle overnight shifts
                if ($slotEnd->lessThan($slotStart)) {
                    $slotEnd->addDay();
                }

                if ($eventStart->between($slotStart->subMinutes(15), $slotEnd->addMinutes(15))) { // Add a 15-minute margin
                    $correctSlot = $slot;
                    break;
                }
            }

            if (!$correctSlot) {
                Log::info("Could not find a matching time slot for event {$event->id}.");
                continue;
            }

            $scheduledEndTime = Carbon::parse($eventStart->format('Y-m-d') . ' ' . $correctSlot['end']);

            // Handle overnight shifts for scheduled end time
            if ($scheduledEndTime->lessThan($eventStart)) {
                $scheduledEndTime->addDay();
            }

            if (Carbon::now()->isAfter($scheduledEndTime)) {
                Log::info("Closing event {$event->id} for user {$user->id}.");
                $event->update([
                    'end' => $scheduledEndTime,
                    'is_open' => false,
                    'is_closed_automatically' => true,
                    'observations' => ($event->observations ? $event->observations . ' ' : '') . __('Closed automatically.'),
                ]);
            }
        }

        Log::info('AutoCloseEvents command finished.');
        $this->info('All applicable events have been closed automatically.');
        return 0;
    }
}
