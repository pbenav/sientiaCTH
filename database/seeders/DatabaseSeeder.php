<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create a user, which will also create a personal team via the UserFactory.
        $user = User::factory()->withPersonalTeam()->create();

        // The TeamCreated event listener will fire, but to be explicit and
        // ensure this seeder runs correctly on its own, we can call it.
        // The listener has a guard to prevent duplicates.
        (new EventTypeSeeder)->run($user->currentTeam->id);

        Event::factory(1)->create([
            'user_id' => $user->id,
        ]);
    }
}
