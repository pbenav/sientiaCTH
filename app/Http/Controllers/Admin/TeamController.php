<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\TransferUserToTeam;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TeamController extends Controller
{
    /**
     * Ensure the user is a global administrator.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->is_admin) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of all teams.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        $teams = Team::with(['owner', 'users'])
            ->whereNotNull('user_id') // Only show teams with valid owners
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->orderBy('name')
            ->paginate(20);

        return view('admin.teams.index', compact('teams', 'search'));
    }

    /**
     * Show the form for editing a team.
     */
    public function edit(Team $team)
    {
        $team->load(['owner', 'users']);
        
        // Get all available roles
        $roles = \Laravel\Jetstream\Jetstream::$roles;
        
        // Get all users for adding members (exclude current members and owner if exists)
        $excludedIds = $team->users->pluck('id')->toArray();
        if ($team->owner) {
            $excludedIds[] = $team->owner->id;
        }
        
        $availableUsers = User::whereNotIn('id', $excludedIds)
            ->orderBy('name')
            ->get();

        // Get all teams for transfer functionality
        $allTeams = Team::orderBy('name')->get();

        // Get pending invitations
        $invitations = $team->teamInvitations()->get();
        
        // Get all users (for assign owner modal)
        $allUsers = User::orderBy('name')->get();

        return view('admin.teams.edit', compact('team', 'roles', 'availableUsers', 'allTeams', 'invitations', 'allUsers'));
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'personal_team' => 'boolean',
            'event_retention_months' => 'nullable|integer|min:1|max:120',
        ]);

        $team->update($validated);

        return redirect()
            ->route('admin.teams.edit', $team)
            ->with('success', __('Team updated successfully.'));
    }

    /**
     * Remove the specified team.
     * Preserves historical event records by setting team_id to null.
     */
    public function destroy(Team $team)
    {
        $teamName = $team->name;
        
        DB::transaction(function () use ($team) {
            // Preserve historical event records by nullifying team_id instead of cascading delete
            DB::table('events')->where('team_id', $team->id)->update(['team_id' => null]);
            
            // Detach all team members
            $team->users()->detach();
            
            // Delete team invitations
            $team->teamInvitations()->delete();
            
            // Delete other team-related data (work centers, event types, etc.)
            // Work centers
            if (Schema::hasTable('work_centers')) {
                DB::table('work_centers')->where('team_id', $team->id)->delete();
            }
            
            // Event types
            if (Schema::hasTable('event_types')) {
                DB::table('event_types')->where('team_id', $team->id)->delete();
            }
            
            // Announcements
            if (Schema::hasTable('announcements')) {
                DB::table('announcements')->where('team_id', $team->id)->delete();
            }
            
            // Team preferences (if exists)
            if (Schema::hasTable('team_preferences')) {
                DB::table('team_preferences')->where('team_id', $team->id)->delete();
            }
            
            // Finally, delete the team itself
            $team->delete();
        });

        return redirect()
            ->route('admin.teams.index')
            ->with('success', __('Team :name deleted successfully.', ['name' => $teamName]));
    }

    /**
     * Add a user to the team.
     */
    public function addMember(Request $request, Team $team)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string',
        ]);

        // Check if user is already a member
        if ($team->users()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', __('User is already a member of this team.'));
        }

        $user = User::findOrFail($validated['user_id']);

        // Add user to team
        $team->users()->attach($validated['user_id'], ['role' => $validated['role']]);

        // Remove user from Welcome team if they are there
        $welcomeTeam = Team::where('name', Team::WELCOME_TEAM_NAME)->first();
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

        return back()->with('success', __('User added to team successfully.'));
    }

    /**
     * Update a team member's role.
     */
    public function updateMemberRole(Request $request, Team $team, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|string',
        ]);

        $team->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', __('User role updated successfully.'));
    }

    /**
     * Remove a user from the team.
     */
    public function removeMember(Team $team, User $user)
    {
        // Prevent removing the team owner
        if ($team->user_id === $user->id) {
            return back()->with('error', __('Cannot remove the team owner.'));
        }

        $team->users()->detach($user->id);

        return back()->with('success', __('User removed from team successfully.'));
    }

    /**
     * Transfer team ownership.
     * Global admins can transfer to themselves even if not a member.
     * When transferring a personal team to global admin, converts it to shared team
     * and moves the original owner to the Welcome team.
     */
    public function transferOwnership(Request $request, Team $team)
    {
        $validated = $request->validate([
            'new_owner_id' => 'required|exists:users,id',
        ]);

        $newOwner = User::findOrFail($validated['new_owner_id']);
        $currentUser = auth()->user();

        // Allow global admin to transfer to themselves even if not a member
        $isTransferToGlobalAdmin = $newOwner->is_admin && $newOwner->id === $currentUser->id;
        
        // Ensure new owner is a member of the team (unless it's global admin transferring to self)
        if (!$isTransferToGlobalAdmin && !$team->users()->where('user_id', $newOwner->id)->exists()) {
            return back()->with('error', __('New owner must be a member of the team.'));
        }

        // Capture values before transaction
        $oldOwnerId = $team->user_id;
        $wasPersonalTeam = $team->personal_team;

        // Transfer ownership
        DB::transaction(function () use (&$team, $newOwner, $isTransferToGlobalAdmin, $oldOwnerId, $wasPersonalTeam) {
            // If transferring to global admin and they're not a member, add them first
            if ($isTransferToGlobalAdmin && !$team->users()->where('user_id', $newOwner->id)->exists()) {
                $team->users()->attach($newOwner->id, ['role' => 'admin']);
            }
            
            // Update team owner and personal_team status
            if ($wasPersonalTeam && $isTransferToGlobalAdmin) {
                // Personal team being transferred to global admin - convert to shared
                $team->user_id = $newOwner->id;
                $team->personal_team = false;
                $team->save();
            } else {
                // Regular transfer or shared team
                $team->user_id = $newOwner->id;
                $team->save();
            }
            
            // If transferring a personal team to global admin, move old owner to Welcome team
            if ($wasPersonalTeam && $isTransferToGlobalAdmin) {
                
                // Move old owner to Welcome team
                if ($oldOwnerId) {
                    // Find existing Welcome team (should exist from migration)
                    $welcomeTeam = Team::where('name', 'Bienvenida')->first();
                    
                    // If not found, try to create it with user ID 1 as owner
                    if (!$welcomeTeam) {
                        $adminUser = User::where('is_admin', true)->first() ?? User::find(1);
                        $welcomeTeam = Team::create([
                            'name' => 'Bienvenida',
                            'user_id' => $adminUser ? $adminUser->id : $newOwner->id,
                            'personal_team' => false,
                        ]);
                    }
                    
                    $oldOwner = User::find($oldOwnerId);
                    if ($oldOwner) {
                        // Add to Welcome team if not already a member
                        if (!$welcomeTeam->users()->where('user_id', $oldOwnerId)->exists()) {
                            $welcomeTeam->users()->attach($oldOwnerId, ['role' => 'user']);
                        }
                        
                        // Set Welcome team as current team
                        $oldOwner->forceFill([
                            'current_team_id' => $welcomeTeam->id,
                        ])->save();
                    }
                }
            }
            
            // Remove new owner from members list (they're now the owner)
            $team->users()->detach($newOwner->id);
            
            // Add old owner as admin member if they exist and aren't already a member
            // (only if not transferred to Welcome team)
            if ($oldOwnerId && !$wasPersonalTeam && !$team->users()->where('user_id', $oldOwnerId)->exists()) {
                $team->users()->attach($oldOwnerId, ['role' => 'admin']);
            }
        });

        // Refresh the team model to ensure the owner relationship is updated
        $team->refresh();
        $team->load('owner', 'users');

        $message = __('Team ownership transferred successfully.');
        if ($wasPersonalTeam && $isTransferToGlobalAdmin) {
            $message .= ' ' . __('Personal team converted to shared team.');
        }

        // Use explicit redirect instead of back() to ensure fresh data is loaded
        return redirect()->route('admin.teams.edit', $team)->with('success', $message);
    }

    /**
     * Assign an owner to a team that has no owner.
     */
    public function assignOwner(Request $request, Team $team)
    {
        $validated = $request->validate([
            'owner_id' => 'required|exists:users,id',
        ]);

        $newOwner = User::findOrFail($validated['owner_id']);

        // Verify that the team doesn't already have a valid owner
        if ($team->user_id && $team->owner) {
            return back()->with('error', __('This team already has an owner. Use transfer ownership instead.'));
        }

        // If the new owner is not a member, add them first as admin
        if (!$team->users()->where('user_id', $newOwner->id)->exists()) {
            $team->users()->attach($newOwner->id, ['role' => 'admin']);
        }

        // Assign owner
        $team->user_id = $newOwner->id;
        $team->save();

        // Remove new owner from members list (they're now the owner)
        $team->users()->detach($newOwner->id);
        
        // Remove from Welcome team if present
        $welcomeTeam = Team::where('name', 'Bienvenida')->first();
        if ($welcomeTeam && $welcomeTeam->id !== $team->id && $welcomeTeam->hasUser($newOwner)) {
            $welcomeTeam->users()->detach($newOwner->id);
        }
        
        // Set this team as current team
        $newOwner->forceFill([
            'current_team_id' => $team->id,
        ])->save();

        return redirect()->route('admin.teams.edit', $team)->with('success', __('Team owner assigned successfully.'));
    }

    /**
     * Transfer a user to another team.
     */
    public function transferUser(Request $request, Team $team, User $user, TransferUserToTeam $transferAction)
    {
        $validated = $request->validate([
            'target_team_id' => 'required|exists:teams,id',
            'role' => 'nullable|string',
        ]);

        $targetTeam = Team::findOrFail($validated['target_team_id']);

        try {
            $transferAction->transfer($user, $targetTeam, $validated['role']);
            return back()->with('success', __('User transferred successfully.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Accept a pending invitation for a user.
     */
    public function acceptInvitation(Team $team, TeamInvitation $invitation)
    {
        // Verify invitation belongs to team
        if ($invitation->team_id !== $team->id) {
            abort(404);
        }

        // Find user by email
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            return back()->with('error', __('User not found with this email. Invitation cannot be accepted.'));
        }

        // Add user to team
        $team->users()->attach($user->id, ['role' => $invitation->role]);

        // Remove user from Welcome team if they are there
        $welcomeTeam = Team::where('name', Team::WELCOME_TEAM_NAME)->first();
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




