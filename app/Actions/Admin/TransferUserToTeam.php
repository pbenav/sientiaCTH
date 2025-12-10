<?php

namespace App\Actions\Admin;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TransferUserToTeam
{
    /**
     * Transfer a user to a new team.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $targetTeam
     * @param  string|null  $role
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function transfer(User $user, Team $targetTeam, ?string $role = null)
    {
        // 1. Validate transfer
        $this->validate($user, $targetTeam);

        DB::transaction(function () use ($user, $targetTeam, $role) {
            $originalTeamId = $user->current_team_id;
            
            // 2. Perform transfer using User model method
            $user->transferToTeam($targetTeam, $role);

            // 3. Log the transfer
            Log::info("User transferred between teams", [
                'user_id' => $user->id,
                'from_team_id' => $originalTeamId,
                'to_team_id' => $targetTeam->id,
                'by_user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Validate the transfer request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Team  $targetTeam
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate(User $user, Team $targetTeam)
    {
        // Cannot transfer to the same team
        if ($user->current_team_id === $targetTeam->id) {
            throw ValidationException::withMessages([
                'team_id' => [__('User is already in this team.')],
            ]);
        }

        // Cannot transfer if user owns the target team (already handled in model, but good to check)
        if ($targetTeam->user_id === $user->id) {
            throw ValidationException::withMessages([
                'team_id' => [__('Cannot transfer user to a team they own.')],
            ]);
        }
        
        // Ensure target team is not a personal team (deprecated concept, but safety check)
        if ($targetTeam->personal_team) {
             // We decided to eliminate personal teams, but if any exist, we treat them as normal teams now.
             // So no specific validation needed here unless we want to block transfers TO personal teams.
             // Given the plan to eliminate them, we allow it (they are just teams now).
        }
    }
}
