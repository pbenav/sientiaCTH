<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\Events\AddingTeamMember;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class AddTeamMember implements AddsTeamMembers
{
    /**
     * Add a new team member to the given team.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    public function add($user, $team, string $email, string $role = null)
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $role);

        $newTeamMember = Jetstream::findUserByEmailOrFail($email);

        // Auto-verify email if the user is being added by a team admin
        // This allows team owners to accept users without them needing to verify their email first
        if (! $newTeamMember->hasVerifiedEmail()) {
            $newTeamMember->markEmailAsVerified();
        }

        AddingTeamMember::dispatch($team, $newTeamMember);

        // Determinar el custom_role_id por defecto según el rol legacy
        $customRoleId = null;
        if ($role === 'admin') {
            // Buscar el rol "Administrador" para este equipo
            $adminRole = \App\Models\Role::where('team_id', $team->id)
                ->where('name', "team_{$team->id}_administrador")
                ->first();
            $customRoleId = $adminRole?->id;
        } elseif ($role === 'user') {
            // Buscar el rol "Usuario" para este equipo
            $userRole = \App\Models\Role::where('team_id', $team->id)
                ->where('name', "team_{$team->id}_usuario")
                ->first();
            $customRoleId = $userRole?->id;
        } elseif ($role === 'inspect') {
            // Buscar el rol "Inspector" para este equipo
            $inspectorRole = \App\Models\Role::where('team_id', $team->id)
                ->where('name', "team_{$team->id}_inspector")
                ->first();
            $customRoleId = $inspectorRole?->id;
        }

        $team->users()->attach(
            $newTeamMember, [
                'role' => $role,
                'custom_role_id' => $customRoleId,
            ]
        );

        // Remove user from Welcome team if they are being added to another team
        $welcomeTeam = \App\Models\Team::where('name', \App\Models\Team::WELCOME_TEAM_NAME)->first();
        if ($welcomeTeam && $welcomeTeam->id !== $team->id && $welcomeTeam->hasUser($newTeamMember)) {
            $welcomeTeam->users()->detach($newTeamMember);
        }

        // If this is the user's first real team (not Welcome), set it as current
        if ($newTeamMember->current_team_id === $welcomeTeam?->id) {
            $newTeamMember->forceFill([
                'current_team_id' => $team->id,
            ])->save();
        }

        // Refresh the teams relationship so the user can immediately switch to this team
        // This ensures belongsToTeam() checks will pass when the user tries to switch teams
        $newTeamMember->load('teams');

        TeamMemberAdded::dispatch($team, $newTeamMember);
    }

    /**
     * Validate the add member operation.
     *
     * @param  mixed  $team
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    protected function validate($team, string $email, ?string $role)
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules(), [
            'email.exists' => __('We were unable to find a registered user with this email address.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnTeam($team, $email)
        )->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for adding a team member.
     *
     * @return array
     */
    protected function rules()
    {
        return array_filter([
            'email' => ['required', 'email', 'exists:users'],
            'role' => Jetstream::hasRoles()
                            ? ['required', 'string', new Role]
                            : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the team.
     *
     * @param  mixed  $team
     * @param  string  $email
     * @return \Closure
     */
    protected function ensureUserIsNotAlreadyOnTeam($team, string $email)
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()->addIf(
                $team->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the team.')
            );
        };
    }
}
