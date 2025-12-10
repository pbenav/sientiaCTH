<?php
$user = App\Models\User::find(1); // Assuming User 1 is global admin
echo "User 1 Is Admin: " . ($user->is_admin ? 'Yes' : 'No') . PHP_EOL;
echo "User 1 All Teams Count: " . $user->allTeams()->count() . PHP_EOL;

$eligible = $user->allTeams()->filter(function ($t) use ($user) {
    return $user->ownsTeam($t) || $user->hasTeamRole($t, 'admin');
});
echo "Eligible Teams (Current Logic): " . $eligible->count() . PHP_EOL;

echo "Total Teams in DB: " . App\Models\Team::count() . PHP_EOL;
