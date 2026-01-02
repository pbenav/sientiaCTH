<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // EVENTOS (Events)
            ['name' => 'events.view.own', 'display_name' => 'Ver propios eventos', 'category' => 'events', 'requires_context' => false],
            ['name' => 'events.view.team', 'display_name' => 'Ver eventos del equipo', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.view.all', 'display_name' => 'Ver todos los eventos', 'category' => 'events', 'requires_context' => false],
            ['name' => 'events.create.own', 'display_name' => 'Crear eventos propios', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.create.team', 'display_name' => 'Crear eventos para otros', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.update.own', 'display_name' => 'Editar propios eventos', 'category' => 'events', 'requires_context' => false],
            ['name' => 'events.update.team', 'display_name' => 'Editar eventos del equipo', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.delete.own', 'display_name' => 'Eliminar propios eventos', 'category' => 'events', 'requires_context' => false],
            ['name' => 'events.delete.team', 'display_name' => 'Eliminar eventos del equipo', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.authorize', 'display_name' => 'Autorizar eventos', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.export', 'display_name' => 'Exportar eventos', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.import', 'display_name' => 'Importar eventos', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.exceptional.create', 'display_name' => 'Crear eventos excepcionales', 'category' => 'events', 'requires_context' => true],
            ['name' => 'events.exceptional.approve', 'display_name' => 'Aprobar eventos excepcionales', 'category' => 'events', 'requires_context' => true],

            // EQUIPOS (Teams)
            ['name' => 'teams.view', 'display_name' => 'Ver equipo', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.create', 'display_name' => 'Crear equipos', 'category' => 'teams', 'requires_context' => false],
            ['name' => 'teams.update', 'display_name' => 'Actualizar equipo', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.delete', 'display_name' => 'Eliminar equipo', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.members.view', 'display_name' => 'Ver miembros', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.members.add', 'display_name' => 'Añadir miembros', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.members.remove', 'display_name' => 'Eliminar miembros', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.members.update', 'display_name' => 'Actualizar roles de miembros', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.invitations.send', 'display_name' => 'Enviar invitaciones', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.invitations.cancel', 'display_name' => 'Cancelar invitaciones', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.settings.view', 'display_name' => 'Ver ajustes', 'category' => 'teams', 'requires_context' => true],
            ['name' => 'teams.settings.update', 'display_name' => 'Actualizar ajustes', 'category' => 'teams', 'requires_context' => true],

            // TIPOS DE EVENTO (Event Types)
            ['name' => 'event_types.view', 'display_name' => 'Ver tipos de evento', 'category' => 'event_types', 'requires_context' => true],
            ['name' => 'event_types.create', 'display_name' => 'Crear tipos de evento', 'category' => 'event_types', 'requires_context' => true],
            ['name' => 'event_types.update', 'display_name' => 'Actualizar tipos de evento', 'category' => 'event_types', 'requires_context' => true],
            ['name' => 'event_types.delete', 'display_name' => 'Eliminar tipos de evento', 'category' => 'event_types', 'requires_context' => true],
            ['name' => 'event_types.manage_colors', 'display_name' => 'Gestionar colores', 'category' => 'event_types', 'requires_context' => true],

            // FESTIVOS (Holidays)
            ['name' => 'holidays.view', 'display_name' => 'Ver festivos', 'category' => 'holidays', 'requires_context' => true],
            ['name' => 'holidays.create', 'display_name' => 'Crear festivos', 'category' => 'holidays', 'requires_context' => true],
            ['name' => 'holidays.update', 'display_name' => 'Actualizar festivos', 'category' => 'holidays', 'requires_context' => true],
            ['name' => 'holidays.delete', 'display_name' => 'Eliminar festivos', 'category' => 'holidays', 'requires_context' => true],
            ['name' => 'holidays.import', 'display_name' => 'Importar festivos', 'category' => 'holidays', 'requires_context' => true],

            // CENTROS DE TRABAJO (Work Centers)
            ['name' => 'work_centers.view', 'display_name' => 'Ver centros de trabajo', 'category' => 'work_centers', 'requires_context' => true],
            ['name' => 'work_centers.create', 'display_name' => 'Crear centros de trabajo', 'category' => 'work_centers', 'requires_context' => true],
            ['name' => 'work_centers.update', 'display_name' => 'Actualizar centros de trabajo', 'category' => 'work_centers', 'requires_context' => true],
            ['name' => 'work_centers.delete', 'display_name' => 'Eliminar centros de trabajo', 'category' => 'work_centers', 'requires_context' => true],

            // ANUNCIOS (Announcements)
            ['name' => 'announcements.view', 'display_name' => 'Ver anuncios', 'category' => 'announcements', 'requires_context' => true],
            ['name' => 'announcements.create', 'display_name' => 'Crear anuncios', 'category' => 'announcements', 'requires_context' => true],
            ['name' => 'announcements.update', 'display_name' => 'Actualizar anuncios', 'category' => 'announcements', 'requires_context' => true],
            ['name' => 'announcements.delete', 'display_name' => 'Eliminar anuncios', 'category' => 'announcements', 'requires_context' => true],
            ['name' => 'announcements.publish', 'display_name' => 'Publicar anuncios', 'category' => 'announcements', 'requires_context' => true],

            // REPORTES (Reports)
            ['name' => 'reports.view.own', 'display_name' => 'Ver propios reportes', 'category' => 'reports', 'requires_context' => true],
            ['name' => 'reports.view.team', 'display_name' => 'Ver reportes del equipo', 'category' => 'reports', 'requires_context' => true],
            ['name' => 'reports.export', 'display_name' => 'Exportar reportes', 'category' => 'reports', 'requires_context' => true],
            ['name' => 'reports.advanced', 'display_name' => 'Reportes avanzados', 'category' => 'reports', 'requires_context' => true],

            // USUARIOS (Users)
            ['name' => 'users.view', 'display_name' => 'Ver usuarios', 'category' => 'users', 'requires_context' => true],
            ['name' => 'users.create', 'display_name' => 'Crear usuarios', 'category' => 'users', 'requires_context' => false],
            ['name' => 'users.update', 'display_name' => 'Actualizar usuarios', 'category' => 'users', 'requires_context' => true],
            ['name' => 'users.delete', 'display_name' => 'Eliminar usuarios', 'category' => 'users', 'requires_context' => false],
            ['name' => 'users.impersonate', 'display_name' => 'Suplantar usuario', 'category' => 'users', 'requires_context' => false, 'is_system' => true],
            ['name' => 'users.manage_schedule', 'display_name' => 'Gestionar horarios', 'category' => 'users', 'requires_context' => true],

            // ROLES Y PERMISOS
            ['name' => 'roles.view', 'display_name' => 'Ver roles', 'category' => 'roles', 'requires_context' => true],
            ['name' => 'roles.create', 'display_name' => 'Crear roles', 'category' => 'roles', 'requires_context' => true],
            ['name' => 'roles.update', 'display_name' => 'Actualizar roles', 'category' => 'roles', 'requires_context' => true],
            ['name' => 'roles.delete', 'display_name' => 'Eliminar roles', 'category' => 'roles', 'requires_context' => true],
            ['name' => 'roles.assign', 'display_name' => 'Asignar roles', 'category' => 'roles', 'requires_context' => true],
            ['name' => 'permissions.grant', 'display_name' => 'Otorgar permisos', 'category' => 'permissions', 'requires_context' => true],
            ['name' => 'permissions.revoke', 'display_name' => 'Revocar permisos', 'category' => 'permissions', 'requires_context' => true],
            ['name' => 'permissions.audit', 'display_name' => 'Ver auditoría de permisos', 'category' => 'permissions', 'requires_context' => true],

            // ADMIN GLOBAL
            ['name' => 'admin.access', 'display_name' => 'Acceso panel admin', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
            ['name' => 'admin.teams.manage', 'display_name' => 'Gestionar todos los equipos', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
            ['name' => 'admin.users.manage', 'display_name' => 'Gestionar todos los usuarios', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
            ['name' => 'admin.system.settings', 'display_name' => 'Ajustes del sistema', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
            ['name' => 'admin.audit.view', 'display_name' => 'Ver logs de auditoría', 'category' => 'admin', 'requires_context' => false, 'is_system' => true],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('✓ ' . count($permissions) . ' permisos creados o actualizados.');
    }
}
