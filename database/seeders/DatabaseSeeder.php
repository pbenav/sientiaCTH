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
        // Create a user. The UserFactory's `withPersonalTeam` method
        // will create a team and dispatch the TeamCreated event.
        // The SeedEventTypes listener will then automatically
        // run the EventTypeSeeder for the new team.
        // $user = User::factory()->withPersonalTeam()->create();

        // Optionally, create a default event for this user.
        // Event::factory(1)->create([
        //     'user_id' => $user->id,
        // ]);
        $this->call(WorkCenterSeeder::class);
    }
}
