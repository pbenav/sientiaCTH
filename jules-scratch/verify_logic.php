<?php

require '/var/www/cth/vendor/autoload.php';
$app = require_once '/var/www/cth/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Team;
use App\Models\Event;
use Illuminate\Support\Facades\Artisan;

// 1. Create users and teams
$owner = User::factory()->create();
$sourceTeam = Team::factory()->create(['user_id' => $owner->id]);
$destinationTeam = Team::factory()->create(['user_id' => $owner->id]);

$userToMove = User::factory()->create();
$userToMove->teams()->attach($sourceTeam->id, ['role' => 'editor']);

// 2. Create events for the user in the source team
Event::factory()->count(5)->create([
    'user_id' => $userToMove->id,
    'team_id' => $sourceTeam->id,
]);

echo "Estado inicial:\n";
echo "- El usuario {$userToMove->id} tiene " . $userToMove->events()->where('team_id', $sourceTeam->id)->count() . " eventos en el equipo {$sourceTeam->id}\n";
echo "- El usuario {$userToMove->id} tiene " . $userToMove->events()->where('team_id', $destinationTeam->id)->count() . " eventos en el equipo {$destinationTeam->id}\n";

// 3. Execute the move logic
$role = optional($userToMove->teamRole($sourceTeam))->key ?? 'editor';
$userToMove->teams()->detach($sourceTeam->id);
$userToMove->teams()->attach($destinationTeam->id, ['role' => $role]);
$userToMove->events()->where('team_id', $sourceTeam->id)->update(['team_id' => $destinationTeam->id]);

echo "\nDespués del movimiento:\n";
echo "- El usuario {$userToMove->id} tiene " . $userToMove->events()->where('team_id', $sourceTeam->id)->count() . " eventos en el equipo {$sourceTeam->id}\n";
echo "- El usuario {$userToMove->id} tiene " . $userToMove->events()->where('team_id', $destinationTeam->id)->count() . " eventos en el equipo {$destinationTeam->id}\n";

// 4. Verify the results
$eventsInSource = $userToMove->events()->where('team_id', $sourceTeam->id)->count();
$eventsInDestination = $userToMove->events()->where('team_id', $destinationTeam->id)->count();

if ($eventsInSource === 0 && $eventsInDestination === 5) {
    echo "\nVerificación exitosa: Todos los eventos se movieron al nuevo equipo.\n";
    exit(0);
} else {
    echo "\nVerificación fallida: La transferencia de eventos no fue exitosa.\n";
    exit(1);
}
