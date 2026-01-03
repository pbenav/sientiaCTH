<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Team;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RolesPermissionsManager extends Component
{
    use WithPagination;

    public $team;
    public $activeTab = 'roles';
    
    // Role management
    public $showRoleModal = false;
    public $editingRole = null;
    public $roleForm = [
        'name' => '',
        'display_name' => '',
        'description' => '',
    ];
    public $selectedRolePermissions = [];
    
    // Permission management
    public $showPermissionModal = false;
    public $editingPermission = null;
    public $permissionForm = [
        'name' => '',
        'display_name' => '',
        'description' => '',
        'category' => '',
        'requires_context' => false,
    ];
    public $searchPermissions = '';
    
    // User permissions
    public $showUserPermissionsModal = false;
    public $selectedUser = null;
    public $userDirectPermissions = [];
    
    // Filters
    public $searchRoles = '';
    public $searchUsers = '';
    public $categoryFilter = '';
    
    protected $queryString = ['activeTab'];

    // No definir $rules aquí para evitar validación global
    // Se validarán campos específicos en cada método

    public function mount(Team $team)
    {
        $this->team = $team;
    }

    public function updatingSearchRoles()
    {
        $this->resetPage();
    }

    public function updatingSearchUsers()
    {
        $this->resetPage();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // ========== ROLE MANAGEMENT ==========

    public function createRole()
    {
        $this->editingRole = null;
        $this->roleForm = [
            'name' => '',
            'display_name' => '',
            'description' => '',
        ];
        $this->selectedRolePermissions = [];
        $this->showRoleModal = true;
    }

    public function editRole($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        
        // Solo roles del equipo o roles de sistema si es admin global
        if ($role->team_id !== $this->team->id && !Auth::user()->is_admin) {
            $this->dispatchBrowserEvent('alertFail', ['message' => __('Unauthorized action')]);
            return;
        }

        $this->editingRole = $role;
        $this->roleForm = [
            'name' => $role->name,
            'display_name' => $role->display_name,
            'description' => $role->description,
        ];
        $this->selectedRolePermissions = $role->permissions->pluck('id')->toArray();
        $this->showRoleModal = true;
    }

    public function toggleCategoryPermissions($category)
    {
        // Obtener los permisos de esta categoría
        $permissions = Permission::where('category', $category)->get();
        $permissionIds = $permissions->pluck('id')->toArray();
        
        // Verificar si al menos uno está seleccionado
        $anySelected = !empty(array_intersect($permissionIds, $this->selectedRolePermissions));
        
        if ($anySelected) {
            // Deseleccionar todos los de esta categoría
            $this->selectedRolePermissions = array_values(array_diff($this->selectedRolePermissions, $permissionIds));
        } else {
            // Seleccionar todos los de esta categoría
            $this->selectedRolePermissions = array_values(array_unique(array_merge($this->selectedRolePermissions, $permissionIds)));
        }
    }

    public function saveRole()
    {
        \Log::info('saveRole called', [
            'roleForm' => $this->roleForm,
            'selectedRolePermissions' => $this->selectedRolePermissions,
            'editingRole' => $this->editingRole ? $this->editingRole->id : null,
        ]);

        try {
            $this->validate([
                'roleForm.display_name' => 'required|string|max:255',
                'roleForm.description' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in saveRole', ['errors' => $e->validator->errors()->all()]);
            $this->dispatchBrowserEvent('alertFail', ['message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all())]);
            return;
        }

        DB::beginTransaction();
        try {
            if ($this->editingRole) {
                // Update existing role
                $role = $this->editingRole;
                
                if ($role->is_system && !Auth::user()->is_admin) {
                    throw new \Exception(__('Cannot edit system roles'));
                }
                
                $role->update([
                    'display_name' => $this->roleForm['display_name'],
                    'description' => $this->roleForm['description'],
                ]);
            } else {
                // Create new role
                \Log::info('Creating new role');
                $role = Role::create([
                    'name' => $this->generateRoleName($this->roleForm['display_name']),
                    'display_name' => $this->roleForm['display_name'],
                    'description' => $this->roleForm['description'],
                    'team_id' => $this->team->id,
                    'is_system' => false,
                    'created_by' => Auth::id(),
                ]);
                \Log::info('Role created', ['role_id' => $role->id]);
            }

            // Sync permissions
            $role->permissions()->sync($this->selectedRolePermissions);

            DB::commit();
            
            \Log::info('Role saved successfully', ['role_id' => $role->id]);
            
            $this->dispatchBrowserEvent('swal:alert', [
                'title' => __('Success'),
                'text' => __('Role saved successfully'),
                'icon' => 'success'
            ]);
            $this->showRoleModal = false;
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving role', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->dispatchBrowserEvent('alertFail', ['message' => $e->getMessage()]);
        }
    }

    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        
        if ($role->is_system) {
            $this->dispatchBrowserEvent('alertFail', ['message' => __('Cannot delete system roles')]);
            return;
        }

        if ($role->team_id !== $this->team->id) {
            $this->dispatchBrowserEvent('alertFail', ['message' => __('Unauthorized action')]);
            return;
        }

        DB::beginTransaction();
        try {
            // Remove role from users
            DB::table('team_user')
                ->where('team_id', $this->team->id)
                ->where('custom_role_id', $role->id)
                ->update(['custom_role_id' => null]);

            $role->delete();
            
            DB::commit();
            $this->dispatchBrowserEvent('swal:alert', [
                'title' => __('Success'),
                'text' => __('Role deleted successfully'),
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchBrowserEvent('alertFail', ['message' => $e->getMessage()]);
        }
    }

    private function generateRoleName($displayName)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $displayName)));
        return 'team_' . $this->team->id . '_' . $slug;
    }

    // ========== USER PERMISSIONS ==========

    public function manageUserPermissions($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        
        // Obtener permisos directos del usuario en este equipo
        $this->userDirectPermissions = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->where('team_id', $this->team->id)
            ->whereNull('revoked_at')
            ->pluck('permission_id')
            ->toArray();
        
        $this->showUserPermissionsModal = true;
    }

    public function saveUserPermissions()
    {
        DB::beginTransaction();
        try {
            // Eliminar permisos existentes
            DB::table('user_permissions')
                ->where('user_id', $this->selectedUser->id)
                ->where('team_id', $this->team->id)
                ->delete();

            // Crear nuevos permisos
            foreach ($this->userDirectPermissions as $permissionId) {
                DB::table('user_permissions')->insert([
                    'user_id' => $this->selectedUser->id,
                    'permission_id' => $permissionId,
                    'team_id' => $this->team->id,
                    'granted_by' => Auth::id(),
                    'granted_at' => now(),
                ]);
            }

            DB::commit();
            $this->dispatchBrowserEvent('swal:alert', [
                'title' => __('Success'),
                'text' => __('User permissions updated successfully'),
                'icon' => 'success'
            ]);
            $this->showUserPermissionsModal = false;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchBrowserEvent('alertFail', ['message' => $e->getMessage()]);
        }
    }

    public function assignRoleToUser($userId, $roleId)
    {
        DB::table('team_user')
            ->where('team_id', $this->team->id)
            ->where('user_id', $userId)
            ->update(['custom_role_id' => $roleId]);
        
        $this->dispatchBrowserEvent('swal:alert', [
            'title' => __('Success'),
            'text' => __('Role assigned successfully'),
            'icon' => 'success'
        ]);
    }    // ========== PERMISSION MANAGEMENT ==========

    public function updatingSearchPermissions()
    {
        $this->resetPage();
    }

    public function createPermission()
    {
        $this->editingPermission = null;
        $this->permissionForm = [
            'name' => '',
            'display_name' => '',
            'description' => '',
            'category' => '',
            'requires_context' => false,
        ];
        $this->showPermissionModal = true;
    }

    public function editPermission($permissionId)
    {
        $permission = Permission::with('roles')->findOrFail($permissionId);
        
        if ($permission->is_system && !Auth::user()->is_admin) {
            $this->dispatchBrowserEvent('alertFail', ['message' => __('Cannot edit system permissions')]);
            return;
        }

        $this->editingPermission = $permission;
        $this->permissionForm = [
            'name' => $permission->name,
            'display_name' => $permission->display_name,
            'description' => $permission->description,
            'category' => $permission->category,
            'requires_context' => $permission->requires_context,
        ];
        $this->showPermissionModal = true;
    }

    public function savePermission()
    {
        $rules = [
            'permissionForm.name' => 'required|string|max:255|unique:permissions,name',
            'permissionForm.display_name' => 'required|string|max:255',
            'permissionForm.description' => 'nullable|string',
            'permissionForm.category' => 'required|string|max:255',
        ];
        
        // Si estamos editando, permitir el mismo nombre
        if ($this->editingPermission) {
            $rules['permissionForm.name'] = 'required|string|max:255|unique:permissions,name,' . $this->editingPermission->id;
        }
        
        $this->validate($rules);

        try {
            if ($this->editingPermission) {
                // Update existing permission
                if ($this->editingPermission->is_system && !Auth::user()->is_admin) {
                    throw new \Exception(__('Cannot edit system permissions'));
                }
                
                $this->editingPermission->update([
                    'display_name' => $this->permissionForm['display_name'],
                    'description' => $this->permissionForm['description'],
                    'category' => $this->permissionForm['category'],
                    'requires_context' => $this->permissionForm['requires_context'],
                ]);
            } else {
                // Create new permission
                Permission::create([
                    'name' => $this->permissionForm['name'],
                    'display_name' => $this->permissionForm['display_name'],
                    'description' => $this->permissionForm['description'],
                    'category' => $this->permissionForm['category'],
                    'requires_context' => $this->permissionForm['requires_context'],
                    'is_system' => false,
                ]);
            }

            $this->dispatchBrowserEvent('swal:alert', [
                'title' => __('Success'),
                'text' => __('Permission saved successfully'),
                'icon' => 'success'
            ]);
            $this->showPermissionModal = false;
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alertFail', ['message' => $e->getMessage()]);
        }
    }

    public function deletePermission($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        
        if ($permission->is_system) {
            $this->dispatchBrowserEvent('alertFail', ['message' => __('Cannot delete system permissions')]);
            return;
        }

        try {
            $permission->delete();
            $this->dispatchBrowserEvent('swal:alert', [
                'title' => __('Success'),
                'text' => __('Permission deleted successfully'),
                'icon' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alertFail', ['message' => $e->getMessage()]);
        }
    }

    // ========== RENDER ==========

    public function render()
    {
        // Permisos agrupados por categoría (para modales)
        $permissions = Permission::orderBy('category')->orderBy('name')->get()->groupBy('category');
        
        // Permisos paginados (para la pestaña de permisos)
        $permissionsQuery = Permission::query()
            ->when($this->searchPermissions, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%'.$this->searchPermissions.'%')
                          ->orWhere('display_name', 'like', '%'.$this->searchPermissions.'%')
                          ->orWhere('category', 'like', '%'.$this->searchPermissions.'%');
                });
            })
            ->when($this->categoryFilter, function($q) {
                $q->where('category', $this->categoryFilter);
            })
            ->orderBy('category')
            ->orderBy('name');

        $permissionsPaginated = $permissionsQuery->paginate(15, ['*'], 'permissionsPage');
        
        // Obtener categorías únicas para el filtro
        $categories = Permission::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
        
        $roles = Role::query()
            ->where(function($q) {
                $q->where('team_id', $this->team->id)
                  ->orWhereNull('team_id'); // System roles
            })
            ->when($this->searchRoles, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%'.$this->searchRoles.'%')
                          ->orWhere('display_name', 'like', '%'.$this->searchRoles.'%');
                });
            })
            ->withCount('permissions')
            ->paginate(10, ['*'], 'rolesPage');

        // Obtener usuarios del equipo con query builder para poder paginar
        $teamUsersQuery = User::whereHas('teams', function($q) {
            $q->where('team_id', $this->team->id);
        });

        if ($this->searchUsers) {
            $teamUsersQuery->where(function($q) {
                $q->where('name', 'like', '%'.$this->searchUsers.'%')
                  ->orWhere('email', 'like', '%'.$this->searchUsers.'%');
            });
        }

        $teamUsers = $teamUsersQuery->paginate(10, ['*'], 'usersPage');

        // Obtener roles asignados a usuarios
        $userRoles = DB::table('team_user')
            ->where('team_id', $this->team->id)
            ->whereIn('user_id', $teamUsers->pluck('id'))
            ->pluck('custom_role_id', 'user_id');

        return view('livewire.roles-permissions-manager', [
            'permissions' => $permissions,
            'permissionsPaginated' => $permissionsPaginated,
            'categories' => $categories,
            'roles' => $roles,
            'teamUsers' => $teamUsers,
            'userRoles' => $userRoles,
        ]);
    }
}

