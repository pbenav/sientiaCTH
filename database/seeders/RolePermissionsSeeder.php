<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Asignando permisos a roles...');

        // Obtener todos los roles
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->command->warn('⚠️  No hay roles en el sistema');
            return;
        }

        foreach ($roles as $role) {
            $permissions = $this->getPermissionsForRole($role->name);
            
            if (!empty($permissions)) {
                // Sincronizar permisos (reemplaza los existentes)
                $permissionIds = Permission::whereIn('name', $permissions)->pluck('id')->toArray();
                $role->permissions()->sync($permissionIds);
                
                $this->command->info("✓ Rol '{$role->name}': " . count($permissionIds) . " permisos asignados");
            }
        }

        $this->command->info('✅ Permisos asignados correctamente');
    }

    /**
     * Define permisos por defecto según el tipo de rol
     */
    private function getPermissionsForRole(string $roleName): array
    {
        // Detectar el tipo de rol por el nombre
        if (str_contains($roleName, 'propietario') || str_contains($roleName, 'owner')) {
            return $this->getOwnerPermissions();
        }
        
        if (str_contains($roleName, 'administrador') || str_contains($roleName, 'admin')) {
            return $this->getAdminPermissions();
        }
        
        if (str_contains($roleName, 'usuario') || str_contains($roleName, 'user') || 
            str_contains($roleName, 'miembro')) {
            return $this->getUserPermissions();
        }

        if (str_contains($roleName, 'inspector') || str_contains($roleName, 'inspect')) {
            return $this->getInspectorPermissions();
        }

        // Rol desconocido - permisos mínimos
        return $this->getUserPermissions();
    }

    /**
     * Permisos para Owner (todos)
     */
    private function getOwnerPermissions(): array
    {
        return [
            // Events - Full access
            'events.view.own', 'events.view.team', 'events.create.own', 'events.create.team',
            'events.update.own', 'events.update.team', 'events.delete.own', 'events.delete.team',
            'events.authorize', 'events.close', 'events.export', 'events.import', 'events.history',
            
            // Teams - Full access
            'teams.view', 'teams.update', 'teams.manage_members', 'teams.manage_roles',
            'teams.invite', 'teams.transfer_ownership', 'teams.view_settings', 'teams.update_settings',
            'teams.manage_work_centers', 'teams.switch',
            
            // Event Types
            'event_types.view', 'event_types.create', 'event_types.update', 'event_types.delete', 'event_types.manage',
            
            // Holidays
            'holidays.view', 'holidays.create', 'holidays.update', 'holidays.delete', 'holidays.import',
            
            // Work Centers
            'work_centers.view', 'work_centers.create', 'work_centers.update', 'work_centers.delete',
            
            // Announcements
            'announcements.view', 'announcements.create', 'announcements.update', 'announcements.delete', 'announcements.manage',
            
            // Reports
            'reports.view', 'reports.generate', 'reports.export', 'reports.schedule',
            
            // Users
            'users.view', 'users.update', 'users.manage_permissions', 'users.impersonate',
            
            // Roles
            'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign',
            
            // Permissions
            'permissions.view', 'permissions.create', 'permissions.manage',
        ];
    }

    /**
     * Permisos para Admin (casi todos excepto transferir propiedad)
     */
    private function getAdminPermissions(): array
    {
        return [
            // Events - Full access
            'events.view.own', 'events.view.team', 'events.create.own', 'events.create.team',
            'events.update.own', 'events.update.team', 'events.delete.own', 'events.delete.team',
            'events.authorize', 'events.close', 'events.export', 'events.import', 'events.history',
            
            // Teams - Limited (no transfer ownership)
            'teams.view', 'teams.update', 'teams.manage_members', 'teams.manage_roles',
            'teams.invite', 'teams.view_settings', 'teams.update_settings',
            'teams.manage_work_centers', 'teams.switch',
            
            // Event Types
            'event_types.view', 'event_types.create', 'event_types.update', 'event_types.delete', 'event_types.manage',
            
            // Holidays
            'holidays.view', 'holidays.create', 'holidays.update', 'holidays.delete', 'holidays.import',
            
            // Work Centers
            'work_centers.view', 'work_centers.create', 'work_centers.update', 'work_centers.delete',
            
            // Announcements
            'announcements.view', 'announcements.create', 'announcements.update', 'announcements.delete', 'announcements.manage',
            
            // Reports
            'reports.view', 'reports.generate', 'reports.export', 'reports.schedule',
            
            // Users
            'users.view', 'users.update', 'users.manage_permissions', 'users.impersonate',
            
            // Roles
            'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign',
            
            // Permissions
            'permissions.view', 'permissions.manage',
        ];
    }

    /**
     * Permisos para Usuario (equivalente a Member - solo lectura y gestión propia)
     */
    private function getUserPermissions(): array
    {
        return [
            // Events - Solo propios
            'events.view.own', 'events.view.team', 'events.create.own', 
            'events.update.own', 'events.delete.own', 'events.export',
            
            // Teams - Solo ver
            'teams.view', 'teams.view_settings', 'teams.switch',
            
            // Event Types - Solo ver
            'event_types.view',
            
            // Holidays - Solo ver
            'holidays.view',
            
            // Work Centers - Solo ver
            'work_centers.view',
            
            // Announcements - Solo ver
            'announcements.view',
            
            // Reports - Ver y generar
            'reports.view', 'reports.generate',
            
            // Users - Solo ver
            'users.view',
            
            // Roles - Solo ver
            'roles.view',
            
            // Permissions - Solo ver
            'permissions.view',
        ];
    }

    /**
     * Permisos para Member (alias de getUserPermissions para compatibilidad)
     */
    private function getMemberPermissions(): array
    {
        return $this->getUserPermissions();
    }

    /**
     * Permisos para Inspector (solo lectura total)
     */
    private function getInspectorPermissions(): array
    {
        return [
            // Solo permisos de visualización
            'events.view.own', 'events.view.team',
            'teams.view', 'teams.view_settings',
            'event_types.view',
            'holidays.view',
            'work_centers.view',
            'announcements.view',
            'reports.view',
            'users.view',
            'roles.view',
            'permissions.view',
        ];
    }
}
