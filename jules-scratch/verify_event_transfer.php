<?php

require '/var/www/cth/vendor/autoload.php';
$app = require_once '/var/www/cth/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Team;
use App\Models\Event;
use Illuminate\Support\Facades\Artisan;

// 1. Set up a clean test environment
Artisan::call('cache:clear');
exec('cd /var/www/cth && sudo composer dump-autoload');
Artisan::call('migrate:fresh');

// 2. Create users and teams
$owner = User::factory()->create();
$sourceTeam = Team::factory()->create(['user_id' => $owner->id]);
$destinationTeam = Team::factory()->create(['user_id' => $owner->id]);

$userToMove = User::factory()->create();
$userToMove->teams()->attach($sourceTeam->id, ['role' => 'editor']);

// 3. Create events for the user in the source team
Event::factory()->count(5)->create([
    'user_id' => $userToMove->id,
    'team_id' => $sourceTeam->id,
]);

echo "Initial state:\n";
echo "- User {$userToMove->id} has " . $userToMove->events()->where('team_id', $sourceTeam->id)->count() . " events in team {$sourceTeam->id}\n";
echo "- User {$userToMove->id} has " . $userToMove->events()->where('team_id', $destinationTeam->id)->count() . " events in team {$destinationTeam->id}\n";

// 4. Execute the move logic
$role = optional($userToMove->teamRole($sourceTeam))->key ?? 'editor';
$userToMove->teams()->detach($sourceTeam->id);
$userToMove->teams()->attach($destinationTeam->id, ['role' => $role]);
$userToMove->events()->where('team_id', $sourceTeam->id)->update(['team_id' => $destinationTeam->id]);

echo "\nAfter move:\n";
echo "- User {$userToMove->id} has " . $userToMove->events()->where('team_id', $sourceTeam->id)->count() . " events in team {$sourceTeam->id}\n";
echo "- User {$userToMove->id} has " . $userToMove->events()->where('team_id', $destinationTeam->id)->count() . " events in team {$destinationTeam->id}\n";

// 5. Verify the results
$eventsInSource = $userToMove->events()->where('team_id', $sourceTeam->id)->count();
$eventsInDestination = $userToMove->events()->where('team_id', $destinationTeam->id)->count();

if ($eventsInSource === 0 && $eventsInDestination === 5) {
    echo "\nVerification successful: All events were moved to the new team.\n";
    exit(0);
} else {
    echo "\nVerification failed: Event transfer was not successful.\n";
    exit(1);
}
