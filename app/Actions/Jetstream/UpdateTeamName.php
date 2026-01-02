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

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        // Only global admins can update the team limit
        if ($user->is_admin) {
            $rules['max_member_teams'] = ['required', 'integer', 'min:0'];
        }

        Validator::make($input, $rules)->validateWithBag('updateTeamName');

        $data = ['name' => $input['name']];

        if ($user->is_admin && isset($input['max_member_teams'])) {
            $data['max_member_teams'] = $input['max_member_teams'];
        }

        $team->forceFill($data)->save();
    }
}
