<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamInvitationController extends Controller
{
    /**
     * Accept a team invitation.
     * Available for team owners and administrators.
     */
    public function accept(Team $team, TeamInvitation $invitation)
    {
        // Verify invitation belongs to team
        if ($invitation->team_id !== $team->id) {
            abort(404);
        }

        // Check if user has permission to add team members
        if (!Gate::check('addTeamMember', $team)) {
            abort(403, __('Unauthorized action'));
        }

        // Find user by email
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            return back()->with('error', __('User not found with this email. Invitation cannot be accepted.'));
        }

        // Add user to team
        $team->users()->attach($user->id, ['role' => $invitation->role]);

        // Remove user from Welcome team if they are there
        $welcomeTeam = Team::where('name', 'Bienvenida')->first();
        if ($welcomeTeam && $welcomeTeam->id !== $team->id && $welcomeTeam->hasUser($user)) {
            $welcomeTeam->users()->detach($user);
        }

        // If this is the user's first real team (not Welcome), set it as current
        if ($user->current_team_id === $welcomeTeam?->id) {
            $user->forceFill([
                'current_team_id' => $team->id,
            ])->save();
        }

        // Verify email if not verified
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Delete invitation
        $invitation->delete();

        return back()->with('success', __('Invitation accepted successfully. User added to team.'));
    }
}
