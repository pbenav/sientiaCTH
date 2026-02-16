<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Event;
use App\Services\SmartClockInService;
use Carbon\Carbon;

class DebugDailyValidation extends Command
{
    protected $signature = 'debug:validation {raw_date?}';
    protected $description = 'Debugs the daily validation logic for all users in the team';

    public function handle()
    {
        $this->info('Starting Daily Validation Debug (ALL USERS SCANNED)...');

        $adminUser = User::where('email', 'like', '%pbenav%')->first()
                 ?? User::where('role', 'admin')->first()
                 ?? User::first();

        if (!$adminUser) {
            $this->error('No admin user found to determine team context.');
            return;
        }
        
        $team = $adminUser->currentTeam;
        $this->info("Context Team: {$team->name} (ID: {$team->id})");
        
        $rawDate = $this->argument('raw_date') ?? now()->format('Y-m-d');
        $this->info("Checking Date: $rawDate");

        // Timezone calculation
        $appTimezone = config('app.timezone');
        $teamTimezone = $team->timezone ?? $appTimezone;
        
        $date = Carbon::parse($rawDate, $teamTimezone);
        $dayStartUTC = $date->copy()->startOfDay()->setTimezone('UTC');
        $dayEndUTC = $date->copy()->endOfDay()->setTimezone('UTC');

        $this->info("Search Range (UTC): {$dayStartUTC->toDateTimeString()} to {$dayEndUTC->toDateTimeString()}");

        // Scan ALL users in team
        $users = $team->allUsers();
        $this->info("Scanning " . $users->count() . " users in team...");

        $foundAny = false;

        foreach ($users as $user) {
            // Find Events for this user
            $events = Event::where('user_id', $user->id)
                ->where('team_id', $team->id)
                // ->where('is_open', false) // NO FILTER
                ->where('start', '>=', $dayStartUTC)
                ->where('start', '<=', $dayEndUTC)
                ->get();

            if ($events->count() > 0) {
                $foundAny = true;
                $this->info("------------------------------------------------");
                $this->info("USER FOUND: {$user->name} (ID: {$user->id}) - {$events->count()} events");
                
                $totalMinutes = 0;
                foreach ($events as $event) {
                    $type = $event->eventType;
                    $typeName = $type ? $type->name : 'No Type';
                    $isWorkday = $type ? ($type->is_workday_type ? 'YES' : 'NO') : 'N/A';
                    $isOpen = $event->is_open ? 'OPEN' : 'CLOSED';
                    
                    $start = Carbon::parse($event->start, 'UTC');
                    $end = $event->end ? Carbon::parse($event->end, 'UTC') : null;
                    
                    $minutes = 0;
                    if ($end) {
                        $minutes = $end->diffInMinutes($start);
                        if ($isWorkday === 'YES') {
                            $totalMinutes += $minutes;
                        }
                    }
        
                    $endStr = $end ? $end->format('H:i') : 'NULL';
                    $this->line("  -> [ID: {$event->id}] {$typeName} ($isOpen, WD:$isWorkday): {$start->format('H:i')} - {$endStr} ($minutes min)");
                }
                $this->info("  -> Total Computed Workday Minutes: $totalMinutes");
                
                // Simulate Validation for this user
                $service = app(SmartClockInService::class);
                // Mock a new event
                $newEvent = new Event([
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'event_type_id' => $events->first()->event_type_id ?? 1,
                    'start' => Carbon::parse("$rawDate 16:00:00", $teamTimezone)->setTimezone('UTC'),
                ]);
                $endTime = Carbon::parse("$rawDate 22:00:00", $teamTimezone)->setTimezone('UTC'); 
                
                $this->line("  -> Simulating +6h event validation...");
                $validation = $service->validateMaxDuration($user, $newEvent, $endTime);
                
                if (!$validation['success']) {
                     $this->info("  -> BLOCKED! (Current: " . ($validation['current_minutes'] ?? '?') . " min)");
                } else {
                     $this->error("  -> ALLOWED (FAILED TO BLOCK)");
                }
            }
        }
        
        if (!$foundAny) {
            $this->error("NO EVENTS FOUND FOR ANY USER TODAY.");
        }
    }
}
