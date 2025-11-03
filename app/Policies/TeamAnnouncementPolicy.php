<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\TeamAnnouncement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamAnnouncementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any announcements for a team.
     */
    public function viewAny(User $user, Team $team): bool
    {
        // Verificar si el usuario es propietario o miembro del equipo
        return $user->id === $team->user_id 
            || \DB::table('team_user')
                ->where('team_id', $team->id)
                ->where('user_id', $user->id)
                ->exists();
    }

    /**
     * Determine if the user can view the announcement.
     */
    public function view(User $user, TeamAnnouncement $announcement): bool
    {
        // Verificar si el usuario es propietario o miembro del equipo
        return $user->id === $announcement->team->user_id
            || \DB::table('team_user')
                ->where('team_id', $announcement->team_id)
                ->where('user_id', $user->id)
                ->exists();
    }

    /**
     * Determine if the user can create announcements.
     */
    public function create(User $user, Team $team): bool
    {
        $isMember = $user->id === $team->user_id 
            || \DB::table('team_user')
                ->where('team_id', $team->id)
                ->where('user_id', $user->id)
                ->exists();
                
        return $isMember
            && ($user->hasTeamPermission($team, 'team:update') || $user->ownsTeam($team));
    }

    /**
     * Determine if the user can update the announcement.
     */
    public function update(User $user, TeamAnnouncement $announcement): bool
    {
        $isMember = $user->id === $announcement->team->user_id
            || \DB::table('team_user')
                ->where('team_id', $announcement->team_id)
                ->where('user_id', $user->id)
                ->exists();
                
        return $isMember
            && ($user->hasTeamPermission($announcement->team, 'team:update') || $user->ownsTeam($announcement->team));
    }

    /**
     * Determine if the user can delete the announcement.
     */
    public function delete(User $user, TeamAnnouncement $announcement): bool
    {
        $isMember = $user->id === $announcement->team->user_id
            || \DB::table('team_user')
                ->where('team_id', $announcement->team_id)
                ->where('user_id', $user->id)
                ->exists();
                
        return $isMember
            && ($user->hasTeamPermission($announcement->team, 'team:update') || $user->ownsTeam($announcement->team));
    }
}
