<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'user_code' => ['required', 'min:8', 'max:10', 'unique:users'],
            'name' => ['required', 'string', 'max:255'],
            'family_name1' => ['required', 'string', 'max:255'],
            'family_name2' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'user_code' => $input['user_code'],
                'name' => $input['name'],
                'family_name1' => $input['family_name1'],
                'family_name2' => $input['family_name2'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]), function (User $user) {
                // Check if user has pending invitations
                $pendingInvitations = TeamInvitation::where('email', $user->email)->get();
                
                if ($pendingInvitations->isNotEmpty()) {
                    // User has pending invitations, accept them
                    $this->acceptPendingInvitations($user, $pendingInvitations);
                } else {
                    // No pending invitations, assign to Welcome team
                    $this->assignToWelcomeTeam($user);
                }
            });
        });
    }

    /**
     * Assign the new user to the Welcome team.
     * Creates the Welcome team if it doesn't exist.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function assignToWelcomeTeam(User $user)
    {
        // Find or create the Welcome team
        $welcomeTeam = Team::firstOrCreate(
            ['name' => Team::WELCOME_TEAM_NAME],
            [
                'user_id' => 1, // Assign to admin user (ID 1)
                'personal_team' => false,
            ]
        );

        // Add user to the Welcome team with default 'user' role
        $welcomeTeam->users()->attach($user->id, ['role' => 'user']);
        
        // Set this as the user's current team
        $user->forceFill([
            'current_team_id' => $welcomeTeam->id,
        ])->save();
    }

    /**
     * Accept all pending team invitations for the user.
     *
     * @param  \App\Models\User  $user
     * @param  \Illuminate\Support\Collection  $invitations
     * @return void
     */
    protected function acceptPendingInvitations(User $user, $invitations)
    {
        $firstTeam = null;

        foreach ($invitations as $invitation) {
            $team = $invitation->team;

            // Add user to team with the invited role
            $team->users()->attach($user->id, ['role' => $invitation->role]);

            // Remember first team to set as current
            if (!$firstTeam) {
                $firstTeam = $team;
            }

            // Delete the invitation
            $invitation->delete();
        }

        // Set first team as current team
        if ($firstTeam) {
            $user->forceFill([
                'current_team_id' => $firstTeam->id,
            ])->save();
        }

        // Mark email as verified since they received the invitation
        $user->markEmailAsVerified();
    }
}

