<?php

require '/var/www/cth/vendor/autoload.php';
$app = require_once '/var/www/cth/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Team;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Support\Facades\Artisan;

// 1. Set up a clean test environment
Artisan::call('migrate:fresh');

// 2. Create users, teams, and event types
$owner = User::factory()->create();
$sourceTeam = Team::factory()->create(['user_id' => $owner->id]);
$destinationTeam = Team::factory()->create(['user_id' => $owner->id]);

$eventType1 = EventType::create(['team_id' => $sourceTeam->id, 'name' => 'Workday', 'is_workday_type' => true, 'color' => '#000000']);
$eventType2 = EventType::create(['team_id' => $sourceTeam->id, 'name' => 'Vacation', 'color' => '#ffffff']);

$userToMove = User::factory()->create();
$userToMove->teams()->attach($sourceTeam->id, ['role' => 'editor']);

// 3. Create events for the user in the source team
Event::factory()->count(3)->create([
    'user_id' => $userToMove->id,
    'team_id' => $sourceTeam->id,
    'event_type_id' => $eventType1->id,
]);
Event::factory()->count(2)->create([
    'user_id' => $userToMove->id,
    'team_id' => $sourceTeam->id,
    'event_type_id' => $eventType2->id,
]);

echo "Estado inicial:\n";
echo "- El usuario {$userToMove->id} tiene " . $userToMove->events()->where('team_id', $sourceTeam->id)->count() . " eventos en el equipo {$sourceTeam->id}\n";

// 4. Execute the move logic
$role = optional($userToMove->teamRole($sourceTeam))->key ?? 'editor';

// Get all unique event types for the user in the source team
$eventTypes = $userToMove->events()
    ->where('team_id', $sourceTeam->id)
    ->with('eventType')
    ->get()
    ->pluck('eventType')
    ->unique();

foreach ($eventTypes as $eventType) {
    if (!$eventType) continue;

    // Check if an event type with the same name already exists in the destination team
    $newEventType = $destinationTeam->eventTypes()->firstOrCreate(
        ['name' => $eventType->name],
        $eventType->toArray()
    );

    // Update the user's events with the new team_id and event_type_id
    $userToMove->events()
        ->where('team_id', $sourceTeam->id)
        ->where('event_type_id', $eventType->id)
        ->update([
            'team_id' => $destinationTeam->id,
            'event_type_id' => $newEventType->id,
        ]);
}

$userToMove->teams()->detach($sourceTeam->id);
$userToMove->teams()->attach($destinationTeam->id, ['role' => $role]);

echo "\nDespués del movimiento:\n";
$eventsInSource = $userToMove->events()->where('team_id', $sourceTeam->id)->count();
$eventsInDestination = $userToMove->events()->where('team_id', $destinationTeam->id)->count();
echo "- El usuario {$userToMove->id} tiene {$eventsInSource} eventos en el equipo {$sourceTeam->id}\n";
echo "- El usuario {$userToMove->id} tiene {$eventsInDestination} eventos en el equipo {$destinationTeam->id}\n";

// 5. Verify the results
$destinationEventType1 = $destinationTeam->eventTypes()->where('name', 'Workday')->first();
$destinationEventType2 = $destinationTeam->eventTypes()->where('name', 'Vacation')->first();

$eventsWithGoodType1 = $userToMove->events()->where('event_type_id', $destinationEventType1->id)->count();
$eventsWithGoodType2 = $userToMove->events()->where('event_type_id', $destinationEventType2->id)->count();

if ($eventsInSource === 0 && $eventsInDestination === 5 && $eventsWithGoodType1 === 3 && $eventsWithGoodType2 === 2) {
    echo "\nVerificación exitosa: Todos los eventos y sus tipos se movieron al nuevo equipo.\n";
    exit(0);
} else {
    echo "\nVerificación fallida: La transferencia de eventos no fue exitosa.\n";
    exit(1);
}
