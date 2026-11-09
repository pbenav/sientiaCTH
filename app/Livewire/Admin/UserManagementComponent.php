<?php

namespace App\Livewire\Admin;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagementComponent extends Component
{
    use WithPagination;

    // Filters and search
    public $search = '';
    public $selectedTeam = '';
    public $selectedRole = '';
    public $perPage = 15;

    // Modal state: Create/Edit
    public $showingUserModal = false;
    public $isEditing = false;
    public $userIdBeingUpdated = null;

    // Form fields
    public $name = '';
    public $family_name1 = '';
    public $family_name2 = '';
    public $dni = '';
    public $user_code = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $vacation_calculation_type = 'natural';
    public $vacation_working_days = 22;
    public $geolocation_enabled = false;
    public $is_admin = false; // Only global admins can touch this
    public $team_id = '';
    public $team_role = 'user';

    // Modal state: Delete / Remove from team
    public $confirmingUserDeletion = false;
    public $userToDeleteId = null;
    public $userToDeleteName = null;
    public $deleteActionType = 'remove_from_team'; // 'remove_from_team' or 'delete_user'
    public $deleteFromTeamId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedTeam' => ['except' => ''],
        'selectedRole' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedTeam()
    {
        $this->resetPage();
    }

    public function updatingSelectedRole()
    {
        $this->resetPage();
    }

    /**
     * Get teams the current user can administer.
     */
    public function getAdministeredTeamsProperty()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        return $currentUser->administeredTeams()->orderBy('name')->get();
    }

    /**
     * Open modal to create a new user.
     */
    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->resetForm();
        $this->isEditing = false;

        $administeredTeams = $this->administeredTeams;
        if ($administeredTeams->isNotEmpty()) {
            $this->team_id = (string) $administeredTeams->first()->id;
        }

        $this->showingUserModal = true;
    }

    /**
     * Open modal to edit an existing user.
     */
    public function openEditModal($userId)
    {
        $this->resetErrorBag();
        $this->resetForm();
        $this->isEditing = true;
        $this->userIdBeingUpdated = $userId;

        /** @var User $currentUser */
        $currentUser = auth()->user();
        $user = User::with(['teams', 'ownedTeams'])->findOrFail($userId);

        // Security check: team admin can only edit users belonging to one of their administered teams
        $allowedTeamIds = $currentUser->administeredTeamIds();
        $userTeamIds = $user->allTeams()->pluck('id')->toArray();
        $commonTeamIds = array_intersect($allowedTeamIds, $userTeamIds);

        if (!$currentUser->is_admin && empty($commonTeamIds)) {
            abort(403, 'No tienes permisos para gestionar a este usuario.');
        }

        // Security check: non-global admins cannot edit global admins
        if ($user->is_admin && !$currentUser->is_admin) {
            abort(403, 'No tienes permisos para modificar a un administrador global.');
        }

        $this->name = $user->name;
        $this->family_name1 = $user->family_name1;
        $this->family_name2 = $user->family_name2 ?? '';
        $this->dni = $user->dni ?? '';
        $this->user_code = $user->user_code ?? '';
        $this->email = $user->email;
        $this->vacation_calculation_type = $user->vacation_calculation_type ?? 'natural';
        $this->vacation_working_days = $user->vacation_working_days ?? 22;
        $this->geolocation_enabled = (bool) $user->geolocation_enabled;
        $this->is_admin = (bool) $user->is_admin;

        // Determine context team
        if ($this->selectedTeam && in_array((int)$this->selectedTeam, $commonTeamIds)) {
            $contextTeamId = (int) $this->selectedTeam;
        } else {
            $contextTeamId = !empty($commonTeamIds) ? reset($commonTeamIds) : ($user->current_team_id ?: null);
        }

        $this->team_id = $contextTeamId ? (string) $contextTeamId : '';

        // Role in this team
        if ($contextTeamId) {
            $team = Team::find($contextTeamId);
            if ($team && $user->ownsTeam($team)) {
                $this->team_role = 'owner';
            } elseif ($team && $membership = $team->users()->where('users.id', $user->id)->first()) {
                $this->team_role = $membership->membership->role ?? 'user';
            } else {
                $this->team_role = 'user';
            }
        }

        $this->showingUserModal = true;
    }

    /**
     * Save user (Create or Update).
     */
    public function saveUser()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $allowedTeamIds = $currentUser->administeredTeamIds();

        // Validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'family_name1' => ['required', 'string', 'max:255'],
            'family_name2' => ['nullable', 'string', 'max:255'],
            'dni' => ['nullable', 'string', 'max:50'],
            'user_code' => [
                'nullable',
                'string',
                'max:50',
                $this->isEditing
                    ? Rule::unique('users', 'user_code')->ignore($this->userIdBeingUpdated)
                    : Rule::unique('users', 'user_code'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                $this->isEditing
                    ? Rule::unique('users', 'email')->ignore($this->userIdBeingUpdated)
                    : Rule::unique('users', 'email'),
            ],
            'vacation_calculation_type' => ['required', Rule::in(['natural', 'working'])],
            'vacation_working_days' => ['required', 'integer', 'min:0', 'max:365'],
            'geolocation_enabled' => ['boolean'],
            'team_id' => ['required', Rule::in(array_map('strval', $allowedTeamIds))],
            'team_role' => ['required', Rule::in(['admin', 'user', 'editor', 'inspect', 'owner'])],
        ];

        if (!$this->isEditing) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        $validated = $this->validate($rules);

        DB::transaction(function () use ($currentUser, $validated) {
            $targetTeam = Team::findOrFail((int) $this->team_id);

            if (!$currentUser->canAdministerTeam($targetTeam)) {
                abort(403, 'No tienes permiso para administrar el equipo seleccionado.');
            }

            if ($this->isEditing) {
                $user = User::findOrFail($this->userIdBeingUpdated);

                // Non-global admins cannot edit global admins
                if ($user->is_admin && !$currentUser->is_admin) {
                    abort(403, 'No tienes permisos para modificar a un administrador global.');
                }

                $userData = [
                    'name' => $this->name,
                    'family_name1' => $this->family_name1,
                    'family_name2' => $this->family_name2 ?: null,
                    'dni' => $this->dni ?: null,
                    'user_code' => $this->user_code ?: null,
                    'email' => $this->email,
                    'vacation_calculation_type' => $this->vacation_calculation_type,
                    'vacation_working_days' => (int) $this->vacation_working_days,
                    'geolocation_enabled' => (bool) $this->geolocation_enabled,
                ];

                if (!empty($this->password)) {
                    $userData['password'] = Hash::make($this->password);
                }

                if ($currentUser->is_admin) {
                    $userData['is_admin'] = (bool) $this->is_admin;
                }

                $user->update($userData);

                // Update team role if user is not the team owner and requested role is not owner
                if (!$user->ownsTeam($targetTeam)) {
                    $newRole = $this->team_role === 'owner' ? 'admin' : $this->team_role;

                    if ($targetTeam->users()->where('users.id', $user->id)->exists()) {
                        $targetTeam->users()->updateExistingPivot($user->id, [
                            'role' => $newRole,
                        ]);
                    } else {
                        // User wasn't in this team yet; attach them
                        $targetTeam->users()->attach($user->id, [
                            'role' => $newRole,
                        ]);
                    }
                }

                session()->flash('message', "Usuario '{$user->name}' actualizado correctamente.");
            } else {
                // Creating new user
                $newUserData = [
                    'name' => $this->name,
                    'family_name1' => $this->family_name1,
                    'family_name2' => $this->family_name2 ?: null,
                    'dni' => $this->dni ?: null,
                    'user_code' => $this->user_code ?: null,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'vacation_calculation_type' => $this->vacation_calculation_type,
                    'vacation_working_days' => (int) $this->vacation_working_days,
                    'geolocation_enabled' => (bool) $this->geolocation_enabled,
                    'current_team_id' => $targetTeam->id,
                    'email_verified_at' => now(),
                ];

                if ($currentUser->is_admin && $this->is_admin) {
                    $newUserData['is_admin'] = true;
                } else {
                    $newUserData['is_admin'] = false;
                }

                $user = User::create($newUserData);

                // Attach to target team
                $assignedRole = ($this->team_role === 'owner') ? 'admin' : $this->team_role;
                $targetTeam->users()->attach($user->id, [
                    'role' => $assignedRole,
                ]);

                session()->flash('message', "Usuario '{$user->name}' creado y asignado al equipo '{$targetTeam->name}'.");
            }
        });

        $this->showingUserModal = false;
        $this->resetForm();
    }

    /**
     * Confirm removal/deletion of a user.
     */
    public function confirmUserDeletion($userId, $actionType = 'remove_from_team', $teamId = null)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $user = User::findOrFail($userId);

        if ($user->is_admin && !$currentUser->is_admin) {
            abort(403, 'No puedes eliminar ni desvincular a un administrador global.');
        }

        if ($user->id === $currentUser->id) {
            session()->flash('error', 'No puedes eliminarte o desvincularte a ti mismo desde este panel.');
            return;
        }

        $this->userToDeleteId = $user->id;
        $this->userToDeleteName = $user->full_name;
        $this->deleteActionType = $actionType;
        $this->deleteFromTeamId = $teamId ?: ($this->selectedTeam ?: null);
        $this->confirmingUserDeletion = true;
    }

    /**
     * Execute removal from team or total account deletion.
     */
    public function deleteUser()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $user = User::findOrFail($this->userToDeleteId);

        if ($user->id === $currentUser->id) {
            $this->confirmingUserDeletion = false;
            return;
        }

        if ($user->is_admin && !$currentUser->is_admin) {
            abort(403, 'Acción denegada.');
        }

        if ($this->deleteActionType === 'delete_user') {
            if (!$currentUser->is_admin) {
                abort(403, 'Solo los administradores globales pueden eliminar cuentas de usuario completamente.');
            }

            // Global delete
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();

            session()->flash('message', "Usuario '{$this->userToDeleteName}' eliminado definitivamente del sistema.");
        } else {
            // Remove from specific team
            $targetTeamId = $this->deleteFromTeamId;
            if (!$targetTeamId) {
                $administered = $currentUser->administeredTeamIds();
                $userTeams = $user->teams()->pluck('teams.id')->toArray();
                $common = array_intersect($administered, $userTeams);
                $targetTeamId = !empty($common) ? reset($common) : null;
            }

            if (!$targetTeamId) {
                session()->flash('error', 'No se ha podido determinar el equipo del que desvincular al usuario.');
                $this->confirmingUserDeletion = false;
                return;
            }

            $team = Team::findOrFail($targetTeamId);
            if (!$currentUser->canAdministerTeam($team)) {
                abort(403, 'No tienes permiso sobre este equipo.');
            }

            if ($user->ownsTeam($team)) {
                session()->flash('error', 'No se puede desvincular al propietario del equipo. Transfiere la propiedad primero.');
                $this->confirmingUserDeletion = false;
                return;
            }

            $team->users()->detach($user->id);

            // If user's current_team_id was this team, switch to another
            if ($user->current_team_id === $team->id) {
                $user->current_team_id = $user->allTeams()->first()?->id;
                $user->save();
            }

            session()->flash('message', "Usuario '{$this->userToDeleteName}' desvinculado del equipo '{$team->name}'.");
        }

        $this->confirmingUserDeletion = false;
        $this->userToDeleteId = null;
        $this->userToDeleteName = null;
    }

    private function resetForm()
    {
        $this->name = '';
        $this->family_name1 = '';
        $this->family_name2 = '';
        $this->dni = '';
        $this->user_code = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->vacation_calculation_type = 'natural';
        $this->vacation_working_days = 22;
        $this->geolocation_enabled = false;
        $this->is_admin = false;
        $this->team_id = '';
        $this->team_role = 'user';
        $this->userIdBeingUpdated = null;
    }

    public function render()
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $administeredTeamIds = $currentUser->administeredTeamIds();

        $query = User::query()->with(['teams', 'ownedTeams']);

        // Scope to administered teams if not global admin
        if (!$currentUser->is_admin) {
            $query->where(function ($q) use ($administeredTeamIds) {
                $q->whereHas('teams', function ($sub) use ($administeredTeamIds) {
                    $sub->whereIn('teams.id', $administeredTeamIds);
                })->orWhereIn('id', function ($sub) use ($administeredTeamIds) {
                    $sub->select('user_id')->from('teams')->whereIn('id', $administeredTeamIds)->whereNotNull('user_id');
                });
            });
        }

        // Filter by selected team
        if ($this->selectedTeam) {
            $teamId = (int) $this->selectedTeam;
            $query->where(function ($q) use ($teamId) {
                $q->whereHas('teams', function ($sub) use ($teamId) {
                    $sub->where('teams.id', $teamId);
                })->orWhereIn('id', function ($sub) use ($teamId) {
                    $sub->select('user_id')->from('teams')->where('id', $teamId)->whereNotNull('user_id');
                });
            });
        }

        // Filter by search
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('family_name1', 'like', $searchTerm)
                    ->orWhere('family_name2', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('dni', 'like', $searchTerm)
                    ->orWhere('user_code', 'like', $searchTerm);
            });
        }

        // Filter by role (admin, user, inspect, editor)
        if ($this->selectedRole) {
            $role = $this->selectedRole;
            $query->where(function ($q) use ($role, $administeredTeamIds) {
                if ($role === 'global_admin') {
                    $q->where('is_admin', true);
                } elseif ($role === 'team_admin') {
                    $q->whereHas('teams', function ($sub) use ($administeredTeamIds) {
                        $sub->whereIn('teams.id', $administeredTeamIds)
                            ->where('team_user.role', 'admin');
                    })->orWhereIn('id', function ($sub) use ($administeredTeamIds) {
                        $sub->select('user_id')->from('teams')->whereIn('id', $administeredTeamIds)->whereNotNull('user_id');
                    });
                } else {
                    $q->whereHas('teams', function ($sub) use ($role, $administeredTeamIds) {
                        $sub->whereIn('teams.id', $administeredTeamIds)
                            ->where('team_user.role', $role);
                    });
                }
            });
        }

        $users = $query->orderBy('name')->paginate($this->perPage);

        return view('livewire.admin.user-management-component', [
            'users' => $users,
            'administeredTeams' => $this->administeredTeams,
            'isGlobalAdmin' => (bool) $currentUser->is_admin,
            'administeredTeamIds' => $administeredTeamIds,
        ])->layout('layouts.app');
    }
}
