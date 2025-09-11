<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Team;
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
        //\App\Models\User::factory(1)->create();
        Team::factory(1)->create();
        $this->call([
            EventTypeSeeder::class,
        ]);
        Event::factory(1)->create();
    }
}
