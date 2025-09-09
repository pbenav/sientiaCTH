<?php

namespace App\Listeners;

use Laravel\Jetstream\Events\TeamCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Database\Seeders\EventTypeSeeder;
use Illuminate\Support\Facades\App;

class SeedEventTypes
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Laravel\Jetstream\Events\TeamCreated  $event
     * @return void
     */
    public function handle(TeamCreated $event)
    {
        if (!App::environment('testing')) {
            if ($event->team->eventTypes()->count() === 0) {
                $seeder = new EventTypeSeeder();
                $seeder->run($event->team->id);
            }
        }
    }
}
