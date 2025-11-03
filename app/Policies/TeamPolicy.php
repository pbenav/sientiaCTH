<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Bypass para administradores globales.
     * Devuelve true si el usuario es admin global (propiedad is_admin o rol 'admin').
     * Devuelve null para que continúe la evaluación normal en caso contrario.
     */
    public function before(User $user, $ability)
    {
        if (property_exists($user, 'is_admin') && $user->is_admin) {
            return true;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function view(User $user, Team $team)
    {
        // Verificar si el usuario es el propietario del equipo
        if ($user->id === $team->user_id) {
            return true;
        }
        
        // Verificar si el usuario es miembro del equipo
        return $user->teams()->where('teams.id', $team->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function update(User $user, Team $team)
    {
        if (!$team) {
            return false;
        }
        return $user->id === $team->user_id || method_exists($user, 'isTeamAdmin') && $user->isTeamAdmin($team);
    }

    /**
     * Determine whether the user can add team members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function addTeamMember(User $user, Team $team)
    {
        return $user->ownsTeam($team) || $user->hasTeamRole($team, 'admin');
    }

    /**
     * Determine whether the user can update team member permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function updateTeamMember(User $user, Team $team)
    {
        return $user->ownsTeam($team) || $user->hasTeamRole($team, 'admin');
    }

    /**
     * Determine whether the user can remove team members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function removeTeamMember(User $user, Team $team)
    {
        return $user->ownsTeam($team) || $user->hasTeamRole($team, 'admin');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return mixed
     */
    public function delete(User $user, Team $team)
    {
        return $user->ownsTeam($team);
    }
}
