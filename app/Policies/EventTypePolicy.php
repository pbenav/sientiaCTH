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
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Team $team)
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EventType  $eventType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, EventType $eventType)
    {
        return $user->ownsTeam($eventType->team);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EventType  $eventType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, EventType $eventType)
    {
        return $user->ownsTeam($eventType->team);
    }
}
