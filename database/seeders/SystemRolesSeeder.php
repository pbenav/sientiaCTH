<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class SystemRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crea roles del sistema (User, Inspector) y los asigna a usuarios existentes
     */
    public function run(): void
    {
        $this->command->info('🔄 Creando roles del sistema...');

        // Obtener todos los equipos
        $teams = Team::all();

        foreach ($teams as $team) {
            $this->command->info("📁 Procesando equipo: {$team->name}");

            // 1. Crear/actualizar rol "Administrador" para este equipo
            $adminRole = Role::updateOrCreate(
                [
                    'name' => "team_{$team->id}_administrador",
                    'team_id' => $team->id,
                ],
                [
                    'display_name' => 'Administrador',
                    'description' => 'Administrador del equipo con permisos completos excepto transferir propiedad',
                    'is_system' => true,
                ]
            );

            // 2. Crear/actualizar rol "Usuario" para este equipo
            $userRole = Role::updateOrCreate(
                [
                    'name' => "team_{$team->id}_usuario",
                    'team_id' => $team->id,
                ],
                [
                    'display_name' => 'Usuario',
                    'description' => 'Usuario estándar con permisos básicos',
                    'is_system' => true,
                ]
            );

            // 3. Crear/actualizar rol "Inspector" para este equipo
            $inspectorRole = Role::updateOrCreate(
                [
                    'name' => "team_{$team->id}_inspector",
                    'team_id' => $team->id,
                ],
                [
                    'display_name' => 'Inspector',
                    'description' => 'Solo puede ver información, sin modificar',
                    'is_system' => true,
                ]
            );

            // Asignar permisos
            $this->assignAdminPermissions($adminRole);
            $this->assignUserPermissions($userRole);
            $this->assignInspectorPermissions($inspectorRole);

            $this->command->info("  ✓ Rol 'Administrador' creado/actualizado");
            $this->command->info("  ✓ Rol 'Usuario' creado/actualizado");
            $this->command->info("  ✓ Rol 'Inspector' creado/actualizado");

            // 4. Actualizar usuarios existentes según su rol legacy
            $this->migrateUsersToRoles($team, $adminRole, $userRole, $inspectorRole);
        }

        $this->command->info('✅ Roles del sistema creados y usuarios migrados');
    }

    /**
     * Asignar permisos al rol Administrador (casi todos excepto transferir propiedad)
     */
    private function assignAdminPermissions(Role $role): void
    {
        $permissions = [
            // Events - Full access
            'events.view.own', 'events.view.team', 'events.create.own', 'events.create.team',
            'events.update.own', 'events.update.team', 'events.delete.own', 'events.delete.team',
            'events.authorize', 'events.close', 'events.export', 'events.import', 'events.history',
            
            // Teams - Limited (no transfer ownership, no delete)
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
            'users.view', 'users.update', 'users.manage_permissions',
            
            // Roles
            'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign',
            
            // Permissions
            'permissions.view', 'permissions.manage',
        ];

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id')->toArray();
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Asignar permisos al rol Usuario (equivalente a member)
     */
    private function assignUserPermissions(Role $role): void
    {
        $permissions = [
            // Events - Solo propios y ver del equipo
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

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id')->toArray();
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Asignar permisos al rol Inspector (solo lectura)
     */
    private function assignInspectorPermissions(Role $role): void
    {
        // Inspector solo tiene permisos de ver
        $permissions = Permission::where('name', 'like', '%.view%')
            ->orWhere('name', 'like', '%.view.%')
            ->pluck('id')
            ->toArray();

        $role->permissions()->sync($permissions);
    }

    /**
     * Migrar usuarios existentes del sistema legacy a los nuevos roles
     */
    private function migrateUsersToRoles(Team $team, Role $adminRole, Role $userRole, Role $inspectorRole): void
    {
        // Obtener todos los miembros del equipo
        $teamUsers = DB::table('team_user')
            ->where('team_id', $team->id)
            ->get();

        $stats = [
            'admin_migrated' => 0,
            'user_to_usuario' => 0,
            'member_to_usuario' => 0,
            'inspect_to_inspector' => 0,
            'owner_kept' => 0,
        ];

        foreach ($teamUsers as $teamUser) {
            $legacyRole = $teamUser->role;

            // Migrar admin al nuevo rol "Administrador"
            if ($legacyRole === 'admin') {
                DB::table('team_user')
                    ->where('user_id', $teamUser->user_id)
                    ->where('team_id', $team->id)
                    ->update(['custom_role_id' => $adminRole->id]);
                $stats['admin_migrated']++;
            }
            // Migrar user y member al nuevo rol "Usuario"
            elseif ($legacyRole === 'user') {
                DB::table('team_user')
                    ->where('user_id', $teamUser->user_id)
                    ->where('team_id', $team->id)
                    ->update(['custom_role_id' => $userRole->id]);
                
                if ($legacyRole === 'user') {
                    $stats['user_to_usuario']++;
                } else {
                    $stats['member_to_usuario']++;
                }
            } 
            // Migrar inspect al nuevo rol "Inspector"
            elseif ($legacyRole === 'inspect') {
                DB::table('team_user')
                    ->where('user_id', $teamUser->user_id)
                    ->where('team_id', $team->id)
                    ->update(['custom_role_id' => $inspectorRole->id]);
                $stats['inspect_to_inspector']++;
            } 
            // Owner se mantiene sin custom_role (usará fallback legacy)
            else {
                $stats['owner_kept']++;
            }
        }

        if ($stats['admin_migrated'] > 0) {
            $this->command->info("  → {$stats['admin_migrated']} admins migrados a rol 'Administrador'");
        }
        if ($stats['user_to_usuario'] > 0 || $stats['member_to_usuario'] > 0) {
            $total = $stats['user_to_usuario'] + $stats['member_to_usuario'];
            $this->command->info("  → {$total} usuarios migrados a rol 'Usuario'");
        }
        if ($stats['inspect_to_inspector'] > 0) {
            $this->command->info("  → {$stats['inspect_to_inspector']} usuarios migrados a rol 'Inspector'");
        }
    }
}
