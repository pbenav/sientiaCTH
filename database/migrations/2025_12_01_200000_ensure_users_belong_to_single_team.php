<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ensures each user belongs to only one team (either as owner or member).
     * Priority: current_team_id > first team found > Welcome team
     */
    public function up(): void
    {
        // Get all users
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            // Get teams where user is owner
            $ownedTeams = DB::table('teams')
                ->where('user_id', $user->id)
                ->pluck('id')
                ->toArray();
            
            // Get teams where user is member
            $memberTeams = DB::table('team_user')
                ->where('user_id', $user->id)
                ->pluck('team_id')
                ->toArray();
            
            // Combine all teams
            $allTeams = array_merge($ownedTeams, $memberTeams);
            
            if (empty($allTeams)) {
                // User doesn't belong to any team, assign to Welcome team
                $welcomeTeam = DB::table('teams')->where('name', 'Bienvenida')->first();
                if ($welcomeTeam) {
                    DB::table('team_user')->insert([
                        'team_id' => $welcomeTeam->id,
                        'user_id' => $user->id,
                        'role' => 'user',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['current_team_id' => $welcomeTeam->id]);
                }
                continue;
            }
            
            // Determine which team to keep
            $teamToKeep = null;
            
            // Priority 1: current_team_id if it exists and user belongs to it
            if ($user->current_team_id && in_array($user->current_team_id, $allTeams)) {
                $teamToKeep = $user->current_team_id;
            }
            // Priority 2: First owned team
            elseif (!empty($ownedTeams)) {
                $teamToKeep = $ownedTeams[0];
            }
            // Priority 3: First member team
            elseif (!empty($memberTeams)) {
                $teamToKeep = $memberTeams[0];
            }
            
            if (!$teamToKeep) {
                continue;
            }
            
            // Update current_team_id if needed
            if ($user->current_team_id !== $teamToKeep) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['current_team_id' => $teamToKeep]);
            }
            
            // Remove user from all other teams (only as member, not as owner)
            $teamsToRemove = array_diff($memberTeams, [$teamToKeep]);
            if (!empty($teamsToRemove)) {
                DB::table('team_user')
                    ->where('user_id', $user->id)
                    ->whereIn('team_id', $teamsToRemove)
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay reversión para esta migración de limpieza
        // Los datos eliminados no se pueden recuperar
    }
};
