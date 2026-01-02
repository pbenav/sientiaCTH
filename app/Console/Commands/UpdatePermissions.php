<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class UpdatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:update 
                            {--force : Force update without confirmation}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update system permissions from seeder (preserves custom permissions)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔄 Updating System Permissions...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Definición de permisos del sistema (igual que en PermissionsSeeder)
        $systemPermissions = $this->getSystemPermissions();

        $stats = [
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'custom' => 0,
        ];

        // Contar permisos personalizados
        $customCount = Permission::where('is_system', false)->count();
        $stats['custom'] = $customCount;

        if ($customCount > 0) {
            $this->info("📌 Found {$customCount} custom permission(s) - these will be preserved");
            $this->newLine();
        }

        if (!$force && !$isDryRun) {
            if (!$this->confirm('Do you want to update system permissions?', true)) {
                $this->info('Operation cancelled');
                return 0;
            }
        }

        $progressBar = $this->output->createProgressBar(count($systemPermissions));
        $progressBar->start();

        foreach ($systemPermissions as $permissionData) {
            $permission = Permission::where('name', $permissionData['name'])->first();

            if ($permission) {
                // Permiso existe - actualizar solo si es de sistema
                if ($permission->is_system) {
                    $changed = false;
                    foreach (['display_name', 'description', 'category', 'requires_context'] as $field) {
                        if ($permission->$field != $permissionData[$field]) {
                            $changed = true;
                            break;
                        }
                    }

                    if ($changed && !$isDryRun) {
                        $permission->update([
                            'display_name' => $permissionData['display_name'],
                            'description' => $permissionData['description'],
                            'category' => $permissionData['category'],
                            'requires_context' => $permissionData['requires_context'],
                        ]);
                        $stats['updated']++;
                    } elseif ($changed) {
                        $stats['updated']++;
                    } else {
                        $stats['unchanged']++;
                    }
                }
            } else {
                // Permiso no existe - crear
                if (!$isDryRun) {
                    Permission::create($permissionData);
                }
                $stats['created']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->info('✅ Update Complete!');
        $this->newLine();
        
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $stats['created']],
                ['Updated', $stats['updated']],
                ['Unchanged', $stats['unchanged']],
                ['Custom (Preserved)', $stats['custom']],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN - no actual changes were made');
            $this->info('Run without --dry-run to apply changes');
        } else {
            // Asignar permisos a roles existentes
            $this->newLine();
            $this->info('🔄 Updating role permissions...');
            $this->call('db:seed', ['--class' => 'RolePermissionsSeeder']);
            
            // Crear roles de sistema y migrar usuarios
            $this->newLine();
            $this->info('🔄 Creating system roles and migrating users...');
            $this->call('db:seed', ['--class' => 'SystemRolesSeeder']);
        }

        return 0;
    }

    /**
     * Get system permissions definition
     */
    private function getSystemPermissions(): array
    {
        return [
            // Events (14)
            ['name' => 'events.view.own', 'display_name' => 'Ver propios eventos', 'description' => 'Ver sus propios registros de eventos', 'category' => 'events', 'requires_context' => false, 'is_system' => true],
            ['name' => 'events.view.team', 'display_name' => 'Ver eventos del equipo', 'description' => 'Ver eventos de cualquier usuario del equipo', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.view.all', 'display_name' => 'Ver todos los eventos', 'description' => 'Ver eventos de todos los equipos (admin)', 'category' => 'events', 'requires_context' => false, 'is_system' => true],
            ['name' => 'events.create.own', 'display_name' => 'Crear eventos propios', 'description' => 'Crear registros de eventos para sí mismo', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.create.team', 'display_name' => 'Crear eventos para otros', 'description' => 'Crear eventos para otros usuarios del equipo', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.update.own', 'display_name' => 'Editar propios eventos', 'description' => 'Modificar sus propios registros de eventos', 'category' => 'events', 'requires_context' => false, 'is_system' => true],
            ['name' => 'events.update.team', 'display_name' => 'Editar eventos del equipo', 'description' => 'Modificar eventos de otros usuarios del equipo', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.delete.own', 'display_name' => 'Eliminar propios eventos', 'description' => 'Eliminar sus propios registros de eventos', 'category' => 'events', 'requires_context' => false, 'is_system' => true],
            ['name' => 'events.delete.team', 'display_name' => 'Eliminar eventos del equipo', 'description' => 'Eliminar eventos de otros usuarios del equipo', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.authorize', 'display_name' => 'Autorizar eventos', 'description' => 'Autorizar o desautorizar eventos autorizables', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.close', 'display_name' => 'Cerrar eventos', 'description' => 'Cerrar o abrir eventos del equipo', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.export', 'display_name' => 'Exportar eventos', 'description' => 'Exportar eventos a formatos externos', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.import', 'display_name' => 'Importar eventos', 'description' => 'Importar eventos desde formatos externos', 'category' => 'events', 'requires_context' => true, 'is_system' => true],
            ['name' => 'events.history', 'display_name' => 'Ver historial de eventos', 'description' => 'Ver el historial de cambios de eventos', 'category' => 'events', 'requires_context' => true, 'is_system' => true],

            // Teams (12)
            ['name' => 'teams.view', 'display_name' => 'Ver equipos', 'description' => 'Ver información de equipos', 'category' => 'teams', 'requires_context' => false, 'is_system' => true],
            ['name' => 'teams.create', 'display_name' => 'Crear equipos', 'description' => 'Crear nuevos equipos', 'category' => 'teams', 'requires_context' => false, 'is_system' => true],
            ['name' => 'teams.update', 'display_name' => 'Editar equipos', 'description' => 'Modificar configuración de equipos', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.delete', 'display_name' => 'Eliminar equipos', 'description' => 'Eliminar equipos', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.manage_members', 'display_name' => 'Gestionar miembros', 'description' => 'Añadir o eliminar miembros del equipo', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.manage_roles', 'display_name' => 'Gestionar roles de miembros', 'description' => 'Cambiar roles de miembros del equipo', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.invite', 'display_name' => 'Invitar usuarios', 'description' => 'Enviar invitaciones al equipo', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.transfer_ownership', 'display_name' => 'Transferir propiedad', 'description' => 'Transferir la propiedad del equipo', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.view_settings', 'display_name' => 'Ver configuración', 'description' => 'Ver la configuración del equipo', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.update_settings', 'display_name' => 'Editar configuración', 'description' => 'Modificar la configuración del equipo', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.manage_work_centers', 'display_name' => 'Gestionar centros de trabajo', 'description' => 'Administrar centros de trabajo del equipo', 'category' => 'teams', 'requires_context' => true, 'is_system' => true],
            ['name' => 'teams.switch', 'display_name' => 'Cambiar de equipo', 'description' => 'Cambiar entre equipos disponibles', 'category' => 'teams', 'requires_context' => false, 'is_system' => true],

            // Event Types (5)
            ['name' => 'event_types.view', 'display_name' => 'Ver tipos de eventos', 'description' => 'Ver tipos de eventos disponibles', 'category' => 'event_types', 'requires_context' => true, 'is_system' => true],
            ['name' => 'event_types.create', 'display_name' => 'Crear tipos de eventos', 'description' => 'Crear nuevos tipos de eventos', 'category' => 'event_types', 'requires_context' => true, 'is_system' => true],
            ['name' => 'event_types.update', 'display_name' => 'Editar tipos de eventos', 'description' => 'Modificar tipos de eventos existentes', 'category' => 'event_types', 'requires_context' => true, 'is_system' => true],
            ['name' => 'event_types.delete', 'display_name' => 'Eliminar tipos de eventos', 'description' => 'Eliminar tipos de eventos', 'category' => 'event_types', 'requires_context' => true, 'is_system' => true],
            ['name' => 'event_types.manage', 'display_name' => 'Gestión completa de tipos', 'description' => 'Gestión completa de tipos de eventos', 'category' => 'event_types', 'requires_context' => true, 'is_system' => true],

            // Holidays (5)
            ['name' => 'holidays.view', 'display_name' => 'Ver festivos', 'description' => 'Ver días festivos', 'category' => 'holidays', 'requires_context' => true, 'is_system' => true],
            ['name' => 'holidays.create', 'display_name' => 'Crear festivos', 'description' => 'Añadir nuevos días festivos', 'category' => 'holidays', 'requires_context' => true, 'is_system' => true],
            ['name' => 'holidays.update', 'display_name' => 'Editar festivos', 'description' => 'Modificar días festivos existentes', 'category' => 'holidays', 'requires_context' => true, 'is_system' => true],
            ['name' => 'holidays.delete', 'display_name' => 'Eliminar festivos', 'description' => 'Eliminar días festivos', 'category' => 'holidays', 'requires_context' => true, 'is_system' => true],
            ['name' => 'holidays.import', 'display_name' => 'Importar festivos', 'description' => 'Importar días festivos desde archivos', 'category' => 'holidays', 'requires_context' => true, 'is_system' => true],

            // Work Centers (4)
            ['name' => 'work_centers.view', 'display_name' => 'Ver centros de trabajo', 'description' => 'Ver centros de trabajo del equipo', 'category' => 'work_centers', 'requires_context' => true, 'is_system' => true],
            ['name' => 'work_centers.create', 'display_name' => 'Crear centros de trabajo', 'description' => 'Crear nuevos centros de trabajo', 'category' => 'work_centers', 'requires_context' => true, 'is_system' => true],
            ['name' => 'work_centers.update', 'display_name' => 'Editar centros de trabajo', 'description' => 'Modificar centros de trabajo existentes', 'category' => 'work_centers', 'requires_context' => true, 'is_system' => true],
            ['name' => 'work_centers.delete', 'display_name' => 'Eliminar centros de trabajo', 'description' => 'Eliminar centros de trabajo', 'category' => 'work_centers', 'requires_context' => true, 'is_system' => true],

            // Announcements (5)
            ['name' => 'announcements.view', 'display_name' => 'Ver anuncios', 'description' => 'Ver anuncios del equipo', 'category' => 'announcements', 'requires_context' => true, 'is_system' => true],
            ['name' => 'announcements.create', 'display_name' => 'Crear anuncios', 'description' => 'Publicar nuevos anuncios', 'category' => 'announcements', 'requires_context' => true, 'is_system' => true],
            ['name' => 'announcements.update', 'display_name' => 'Editar anuncios', 'description' => 'Modificar anuncios existentes', 'category' => 'announcements', 'requires_context' => true, 'is_system' => true],
            ['name' => 'announcements.delete', 'display_name' => 'Eliminar anuncios', 'description' => 'Eliminar anuncios', 'category' => 'announcements', 'requires_context' => true, 'is_system' => true],
            ['name' => 'announcements.manage', 'display_name' => 'Gestión completa de anuncios', 'description' => 'Gestión completa de anuncios del equipo', 'category' => 'announcements', 'requires_context' => true, 'is_system' => true],

            // Reports (4)
            ['name' => 'reports.view', 'display_name' => 'Ver informes', 'description' => 'Ver informes del equipo', 'category' => 'reports', 'requires_context' => true, 'is_system' => true],
            ['name' => 'reports.generate', 'display_name' => 'Generar informes', 'description' => 'Generar nuevos informes', 'category' => 'reports', 'requires_context' => true, 'is_system' => true],
            ['name' => 'reports.export', 'display_name' => 'Exportar informes', 'description' => 'Exportar informes a diferentes formatos', 'category' => 'reports', 'requires_context' => true, 'is_system' => true],
            ['name' => 'reports.schedule', 'display_name' => 'Programar informes', 'description' => 'Programar generación automática de informes', 'category' => 'reports', 'requires_context' => true, 'is_system' => true],

            // Users (6)
            ['name' => 'users.view', 'display_name' => 'Ver usuarios', 'description' => 'Ver información de usuarios del equipo', 'category' => 'users', 'requires_context' => true, 'is_system' => true],
            ['name' => 'users.create', 'display_name' => 'Crear usuarios', 'description' => 'Crear nuevos usuarios', 'category' => 'users', 'requires_context' => false, 'is_system' => true],
            ['name' => 'users.update', 'display_name' => 'Editar usuarios', 'description' => 'Modificar información de usuarios', 'category' => 'users', 'requires_context' => true, 'is_system' => true],
            ['name' => 'users.delete', 'display_name' => 'Eliminar usuarios', 'description' => 'Eliminar usuarios del sistema', 'category' => 'users', 'requires_context' => true, 'is_system' => true],
            ['name' => 'users.impersonate', 'display_name' => 'Suplantar usuarios', 'description' => 'Ver la aplicación como otro usuario', 'category' => 'users', 'requires_context' => false, 'is_system' => true],
            ['name' => 'users.manage_permissions', 'display_name' => 'Gestionar permisos de usuario', 'description' => 'Asignar permisos directos a usuarios', 'category' => 'users', 'requires_context' => true, 'is_system' => true],

            // Roles (5)
            ['name' => 'roles.view', 'display_name' => 'Ver roles', 'description' => 'Ver roles del equipo', 'category' => 'roles', 'requires_context' => true, 'is_system' => true],
            ['name' => 'roles.create', 'display_name' => 'Crear roles', 'description' => 'Crear nuevos roles personalizados', 'category' => 'roles', 'requires_context' => true, 'is_system' => true],
            ['name' => 'roles.update', 'display_name' => 'Editar roles', 'description' => 'Modificar roles existentes', 'category' => 'roles', 'requires_context' => true, 'is_system' => true],
            ['name' => 'roles.delete', 'display_name' => 'Eliminar roles', 'description' => 'Eliminar roles personalizados', 'category' => 'roles', 'requires_context' => true, 'is_system' => true],
            ['name' => 'roles.assign', 'display_name' => 'Asignar roles', 'description' => 'Asignar roles a usuarios del equipo', 'category' => 'roles', 'requires_context' => true, 'is_system' => true],

            // Permissions (3)
            ['name' => 'permissions.view', 'display_name' => 'Ver permisos', 'description' => 'Ver permisos disponibles', 'category' => 'permissions', 'requires_context' => false, 'is_system' => true],
            ['name' => 'permissions.create', 'display_name' => 'Crear permisos', 'description' => 'Crear nuevos permisos personalizados', 'category' => 'permissions', 'requires_context' => false, 'is_system' => true],
            ['name' => 'permissions.manage', 'display_name' => 'Gestionar permisos', 'description' => 'Gestión completa de permisos del sistema', 'category' => 'permissions', 'requires_context' => false, 'is_system' => true],

            // Admin (5)
            ['name' => 'admin.access', 'display_name' => 'Acceder a admin', 'description' => 'Acceder al panel de administración', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
            ['name' => 'admin.settings', 'display_name' => 'Configuración del sistema', 'description' => 'Modificar configuración global del sistema', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
            ['name' => 'admin.audit', 'display_name' => 'Ver auditoría', 'description' => 'Ver registros de auditoría del sistema', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
            ['name' => 'admin.mail', 'display_name' => 'Configurar correo', 'description' => 'Configurar ajustes de correo electrónico', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
            ['name' => 'admin.teams', 'display_name' => 'Gestionar todos los equipos', 'description' => 'Administrar todos los equipos del sistema', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
        ];
    }
}
