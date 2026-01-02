<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class UpdateTeamName implements UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  array  $input
     * @return void
     */
    public function update($user, $team, array $input)
    {
        Gate::forUser($user)->authorize('update', $team);

        $canManageTeamLimit = $user->is_admin
            || $user->hasPermission('teams.limits.manage', ['team_id' => $team->id]);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        if ($canManageTeamLimit) {
            $rules['max_member_teams'] = ['required', 'integer', 'min:0'];
        }

        Validator::make($input, $rules)->validateWithBag('updateTeamName');

        $data = ['name' => $input['name']];

        if ($canManageTeamLimit && isset($input['max_member_teams'])) {
            $data['max_member_teams'] = $input['max_member_teams'];
        }

        $team->forceFill($data)->save();
    }
}
