<?php

/**
 * Verification script for team ownership transfer
 * 
 * This script helps verify that team ownership transfer works correctly
 * by showing the current state of a team before and after transfer.
 * 
 * Usage: php verify_team_ownership.php <team_id>
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Team;
use App\Models\User;

if ($argc < 2) {
    echo "Usage: php verify_team_ownership.php <team_id>\n";
    exit(1);
}

$teamId = $argv[1];

$team = Team::with(['owner', 'users'])->find($teamId);

if (!$team) {
    echo "Team with ID {$teamId} not found.\n";
    exit(1);
}

echo "=== Team Ownership Information ===\n";
echo "Team ID: {$team->id}\n";
echo "Team Name: {$team->name}\n";
echo "Personal Team: " . ($team->personal_team ? 'Yes' : 'No') . "\n";
echo "Owner ID (user_id): {$team->user_id}\n";

if ($team->owner) {
    echo "Owner Name: {$team->owner->name} {$team->owner->family_name1}\n";
    echo "Owner Email: {$team->owner->email}\n";
    echo "Owner is Admin: " . ($team->owner->is_admin ? 'Yes' : 'No') . "\n";
} else {
    echo "Owner: NOT FOUND (orphaned team)\n";
}

echo "\n=== Team Members ===\n";
if ($team->users->count() > 0) {
    foreach ($team->users as $user) {
        echo "- {$user->name} {$user->family_name1} ({$user->email}) - Role: {$user->membership->role}\n";
    }
} else {
    echo "No members\n";
}

echo "\n=== Database Verification ===\n";
$dbTeam = DB::table('teams')->where('id', $teamId)->first();
echo "Database user_id: {$dbTeam->user_id}\n";
echo "Database name: {$dbTeam->name}\n";
echo "Database personal_team: {$dbTeam->personal_team}\n";

echo "\n=== Available Global Admins ===\n";
$admins = User::where('is_admin', true)->get();
foreach ($admins as $admin) {
    echo "- ID: {$admin->id} - {$admin->name} {$admin->family_name1} ({$admin->email})\n";
}
