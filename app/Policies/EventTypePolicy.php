<?php

namespace App\Policies;

use App\Models\EventType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventTypePolicy
{
    use HandlesAuthorization;

    /**
     * Bypass para administradores globales.
     */
    public function before(User $user, $ability)
    {
        if ($user->is_admin) {
            return true;
        }
        
        return null;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Team $team): bool
    {
        return $user->id === $team->user_id || method_exists($user, 'isTeamAdmin') && $user->isTeamAdmin($team);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EventType  $eventType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, EventType $eventType): bool
    {
        $team = $eventType->team ?? $eventType->team()->first();
        if (!$team) {
            return false;
        }
        return $user->id === $team->user_id || method_exists($user, 'isTeamAdmin') && $user->isTeamAdmin($team);
    }
    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EventType  $eventType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, EventType $eventType): bool
    {
        $team = $eventType->team ?? $eventType->team()->first();
        if (!$team) {
            return false;
        }
        return $user->id === $team->user_id || method_exists($user, 'isTeamAdmin') && $user->isTeamAdmin($team);
    }
}
