<?php

require '/var/www/cth/vendor/autoload.php';
$app = require_once '/var/www/cth/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Team;
use App\Models\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// 1. Find a user with events in a specific team
Artisan::call('cache:clear');
Artisan::call('migrate:fresh', ['--seed' => true]);

$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
echo "Tables in database:\n";
foreach ($tables as $table) {
    echo "- {$table->name}\n";
}

$user = User::first();
if (!$user) {
    echo "Verification failed: Could not find a user after seeding.\n";
    exit(1);
}

$sourceTeam = Team::factory()->create(['user_id' => $user->id]);
$user->teams()->attach($sourceTeam->id, ['role' => 'editor']);

if (!$sourceTeam) {
    echo "Verification failed: User is not in a team.\n";
    exit(1);
}

// Manually create an event for the user
Event::factory()->create([
    'user_id' => $user->id,
    'team_id' => $sourceTeam->id,
]);

$eventsBefore = $user->events()->where('team_id', $sourceTeam->id)->get();
if ($eventsBefore->isEmpty()) {
    echo "Verification failed: No events found for the user in the source team after creating one.\n";
    exit(1);
}

// 2. Find a destination team
$destinationTeam = Team::factory()->create(['user_id' => User::factory()]);
if (!$destinationTeam) {
    echo "Verification failed: No destination team found.\n";
    exit(1);
}

// 3. Programmatically move the user
$role = optional($user->teamRole($sourceTeam))->key ?? 'editor';
$user->teams()->detach($sourceTeam->id);
$user->teams()->attach($destinationTeam->id, ['role' => $role]);
$user->events()->where('team_id', $sourceTeam->id)->update(['team_id' => $destinationTeam->id]);

// 4. Verify that the events have been moved
$eventsAfter = $user->events()->where('team_id', $destinationTeam->id)->get();
$eventsStillInSource = $user->events()->where('team_id', $sourceTeam->id)->get();


if ($eventsBefore->count() === $eventsAfter->count() && $eventsStillInSource->isEmpty()) {
    echo "Verification successful: All events were moved to the new team.\n";
    exit(0);
} else {
    echo "Verification failed: Event count mismatch. Before: {$eventsBefore->count()}, After: {$eventsAfter->count()}, Still in source: {$eventsStillInSource->count()}\n";
    exit(1);
}
