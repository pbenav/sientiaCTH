<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Team;
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
    protected $description = 'Automatically closes unconfirmed events that have passed their expiration date.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Starting AutoCloseEvents command...');

        $teams = Team::whereNotNull('event_expiration_days')->get();

        foreach ($teams as $team) {
            $expirationDays = $team->event_expiration_days;
            $expirationDate = Carbon::now()->subDays($expirationDays);

            $events = Event::where('is_authorized', false)
                ->where('created_at', '<=', $expirationDate)
                ->whereHas('user.teams', function ($query) use ($team) {
                    $query->where('team_id', $team->id);
                })
                ->get();

            foreach ($events as $event) {
                Log::info("Closing event {$event->id} for user {$event->user->id} in team {$team->id}.");
                $event->update([
                    'end' => $event->start,
                    'is_open' => false,
                    'is_closed_automatically' => true,
                    'observations' => ($event->observations ? $event->observations . ' ' : '') . __('Closed automatically due to expiration.'),
                ]);
            }
        }

        Log::info('AutoCloseEvents command finished.');
        $this->info('All applicable events have been closed automatically.');
        return 0;
    }
}
