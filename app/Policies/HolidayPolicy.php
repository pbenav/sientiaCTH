<?php

namespace App\Policies;

use App\Models\Holiday;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HolidayPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Team $team): bool
    {
        return $user->isTeamAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Holiday $holiday): bool
    {
        return $user->isTeamAdmin() && $user->current_team_id === $holiday->team_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->isTeamAdmin() && $user->current_team_id === $holiday->team_id;
    }
}
