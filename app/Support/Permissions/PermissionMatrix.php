<?php

namespace App\Support\Permissions;

use Illuminate\Support\Facades\DB;

class PermissionMatrix
{
    /**
     * Return the canonical permission definitions.
     */
    public static function definitions(): array
    {
        return [
            'events.view.own' => [
                'display_name' => 'Ver eventos propios',
                'description' => 'Permite consultar los registros creados por el propio usuario.',
                'category' => 'events',
                'requires_context' => false,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'events.view.team' => [
                'display_name' => 'Ver eventos del equipo',
                'description' => 'Permite revisar los fichajes de todos los miembros del equipo activo.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'events.view.all' => [
                'display_name' => 'Ver eventos globales',
                'description' => 'Accede al historico de eventos de cualquier equipo.',
                'category' => 'events',
                'requires_context' => false,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'events.create.own' => [
                'display_name' => 'Crear eventos propios',
                'description' => 'Permite registrar fichajes en nombre propio.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'usuario'],
            ],
            'events.create.team' => [
                'display_name' => 'Crear eventos para el equipo',
                'description' => 'Autoriza la creacion de fichajes para otros miembros.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'events.update.own' => [
                'display_name' => 'Editar eventos propios',
                'description' => 'Permite corregir los fichajes personales.',
                'category' => 'events',
                'requires_context' => false,
                'is_system' => true,
                'roles' => ['administrador', 'usuario'],
            ],
            'events.update.team' => [
                'display_name' => 'Editar eventos del equipo',
                'description' => 'Permite actualizar los fichajes de cualquier miembro del equipo.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'events.delete.own' => [
                'display_name' => 'Eliminar eventos propios',
                'description' => 'Autoriza eliminar fichajes creados por el propio usuario.',
                'category' => 'events',
                'requires_context' => false,
                'is_system' => true,
                'roles' => ['administrador', 'usuario'],
            ],
            'events.delete.team' => [
                'display_name' => 'Eliminar eventos del equipo',
                'description' => 'Permite eliminar registros de cualquier miembro del equipo.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'events.authorize' => [
                'display_name' => 'Autorizar eventos',
                'description' => 'Permite aprobar o rechazar eventos que requieren validacion.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'events.export' => [
                'display_name' => 'Exportar eventos',
                'description' => 'Permite descargar los fichajes en diferentes formatos.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'events.import' => [
                'display_name' => 'Importar eventos',
                'description' => 'Autoriza la carga masiva de fichajes.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'events.exceptional.create' => [
                'display_name' => 'Crear eventos excepcionales',
                'description' => 'Permite registrar incidencias o fichajes extraordinarios.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'events.exceptional.approve' => [
                'display_name' => 'Aprobar eventos excepcionales',
                'description' => 'Autoriza revisar y aprobar fichajes extraordinarios.',
                'category' => 'events',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.view' => [
                'display_name' => 'Ver equipo',
                'description' => 'Permite acceder a la informacion basica del equipo.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'teams.create' => [
                'display_name' => 'Crear equipos',
                'description' => 'Autoriza la creacion de nuevos equipos en la plataforma.',
                'category' => 'teams',
                'requires_context' => false,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.update' => [
                'display_name' => 'Actualizar equipo',
                'description' => 'Permite modificar la configuracion general del equipo.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.delete' => [
                'display_name' => 'Eliminar equipo',
                'description' => 'Autoriza la eliminacion definitiva del equipo.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.members.view' => [
                'display_name' => 'Ver miembros',
                'description' => 'Permite consultar la lista de miembros del equipo.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector'],
            ],
            'teams.members.add' => [
                'display_name' => 'Agregar miembros',
                'description' => 'Autoriza invitar o asignar nuevos usuarios al equipo.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.members.remove' => [
                'display_name' => 'Eliminar miembros',
                'description' => 'Permite dar de baja a usuarios del equipo.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.members.update' => [
                'display_name' => 'Actualizar roles de miembros',
                'description' => 'Autoriza modificar el rol granular de los miembros.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.invitations.send' => [
                'display_name' => 'Enviar invitaciones',
                'description' => 'Permite invitar usuarios externos al equipo.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.invitations.cancel' => [
                'display_name' => 'Cancelar invitaciones',
                'description' => 'Autoriza revocar invitaciones pendientes.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.settings.view' => [
                'display_name' => 'Ver ajustes del equipo',
                'description' => 'Permite consultar los ajustes generales y de cumplimiento.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'teams.settings.update' => [
                'display_name' => 'Editar ajustes del equipo',
                'description' => 'Autoriza actualizar preferencias de control horario y formato.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'teams.limits.manage' => [
                'display_name' => 'Gestionar limite de creacion de equipos',
                'description' => 'Permite definir cuantos equipos adicionales puede crear cada miembro.',
                'category' => 'teams',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'event_types.view' => [
                'display_name' => 'Ver tipos de evento',
                'description' => 'Permite consultar la tipologia disponible para el equipo.',
                'category' => 'event_types',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'event_types.create' => [
                'display_name' => 'Crear tipos de evento',
                'description' => 'Autoriza definir nuevas categorias de fichaje.',
                'category' => 'event_types',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'event_types.update' => [
                'display_name' => 'Editar tipos de evento',
                'description' => 'Permite modificar los tipos existentes.',
                'category' => 'event_types',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'event_types.delete' => [
                'display_name' => 'Eliminar tipos de evento',
                'description' => 'Autoriza eliminar categorias que ya no se usan.',
                'category' => 'event_types',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'event_types.manage_colors' => [
                'display_name' => 'Gestionar colores de evento',
                'description' => 'Permite definir la paleta utilizada por cada tipo.',
                'category' => 'event_types',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'holidays.view' => [
                'display_name' => 'Ver festivos',
                'description' => 'Permite consultar el calendario de festivos.',
                'category' => 'holidays',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'holidays.create' => [
                'display_name' => 'Crear festivos',
                'description' => 'Autoriza añadir nuevos dias festivos.',
                'category' => 'holidays',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'holidays.update' => [
                'display_name' => 'Editar festivos',
                'description' => 'Permite modificar dias festivos existentes.',
                'category' => 'holidays',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'holidays.delete' => [
                'display_name' => 'Eliminar festivos',
                'description' => 'Autoriza eliminar dias festivos obsoletos.',
                'category' => 'holidays',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'holidays.import' => [
                'display_name' => 'Importar festivos',
                'description' => 'Permite cargar calendarios de festivos desde ficheros.',
                'category' => 'holidays',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'work_centers.view' => [
                'display_name' => 'Ver centros de trabajo',
                'description' => 'Permite consultar los centros disponibles para fichar.',
                'category' => 'work_centers',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'work_centers.create' => [
                'display_name' => 'Crear centros de trabajo',
                'description' => 'Autoriza dar de alta nuevos centros o sedes.',
                'category' => 'work_centers',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'work_centers.update' => [
                'display_name' => 'Editar centros de trabajo',
                'description' => 'Permite modificar los datos de un centro existente.',
                'category' => 'work_centers',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'work_centers.delete' => [
                'display_name' => 'Eliminar centros de trabajo',
                'description' => 'Autoriza eliminar centros en desuso.',
                'category' => 'work_centers',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'announcements.view' => [
                'display_name' => 'Ver anuncios',
                'description' => 'Permite leer los avisos publicados para el equipo.',
                'category' => 'announcements',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario', 'editor'],
            ],
            'announcements.create' => [
                'display_name' => 'Crear anuncios',
                'description' => 'Autoriza publicar nuevos avisos.',
                'category' => 'announcements',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'editor'],
            ],
            'announcements.update' => [
                'display_name' => 'Editar anuncios',
                'description' => 'Permite modificar avisos existentes.',
                'category' => 'announcements',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'editor'],
            ],
            'announcements.delete' => [
                'display_name' => 'Eliminar anuncios',
                'description' => 'Autoriza retirar avisos publicados.',
                'category' => 'announcements',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'editor'],
            ],
            'announcements.publish' => [
                'display_name' => 'Publicar o pausar anuncios',
                'description' => 'Permite activar o desactivar anuncios visibles.',
                'category' => 'announcements',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'editor'],
            ],
            'reports.view.own' => [
                'display_name' => 'Ver informes propios',
                'description' => 'Autoriza consultar informes personales.',
                'category' => 'reports',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'reports.view.team' => [
                'display_name' => 'Ver informes del equipo',
                'description' => 'Permite acceder a informes agregados del equipo.',
                'category' => 'reports',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector'],
            ],
            'reports.export' => [
                'display_name' => 'Exportar informes',
                'description' => 'Autoriza descargar informes en diferentes formatos.',
                'category' => 'reports',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector'],
            ],
            'reports.advanced' => [
                'display_name' => 'Informes avanzados',
                'description' => 'Permite acceder a funcionalidades avanzadas de reporting.',
                'category' => 'reports',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'users.view' => [
                'display_name' => 'Ver usuarios',
                'description' => 'Permite consultar informacion basica de los usuarios del equipo.',
                'category' => 'users',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'users.create' => [
                'display_name' => 'Crear usuarios',
                'description' => 'Autoriza dar de alta usuarios a nivel global.',
                'category' => 'users',
                'requires_context' => false,
                'is_system' => true,
                'roles' => [],
            ],
            'users.update' => [
                'display_name' => 'Editar usuarios',
                'description' => 'Permite modificar datos de los usuarios del equipo.',
                'category' => 'users',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'users.delete' => [
                'display_name' => 'Eliminar usuarios',
                'description' => 'Autoriza eliminar usuarios del sistema.',
                'category' => 'users',
                'requires_context' => false,
                'is_system' => true,
                'roles' => [],
            ],
            'users.impersonate' => [
                'display_name' => 'Suplantar usuarios',
                'description' => 'Permite acceder temporalmente como otro usuario.',
                'category' => 'users',
                'requires_context' => false,
                'is_system' => true,
                'roles' => [],
            ],
            'users.manage_schedule' => [
                'display_name' => 'Gestionar horarios',
                'description' => 'Autoriza configurar los horarios laborales de los miembros.',
                'category' => 'users',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'roles.view' => [
                'display_name' => 'Ver roles',
                'description' => 'Permite consultar los roles disponibles y sus permisos.',
                'category' => 'roles',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'roles.create' => [
                'display_name' => 'Crear roles',
                'description' => 'Autoriza crear nuevos roles personalizados.',
                'category' => 'roles',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'roles.update' => [
                'display_name' => 'Editar roles',
                'description' => 'Permite actualizar roles existentes.',
                'category' => 'roles',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'roles.delete' => [
                'display_name' => 'Eliminar roles',
                'description' => 'Autoriza eliminar roles personalizados.',
                'category' => 'roles',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'roles.assign' => [
                'display_name' => 'Asignar roles',
                'description' => 'Permite asignar roles a los usuarios del equipo.',
                'category' => 'roles',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'permissions.view' => [
                'display_name' => 'Ver permisos',
                'description' => 'Permite consultar los permisos disponibles.',
                'category' => 'permissions',
                'requires_context' => false,
                'is_system' => true,
                'roles' => ['administrador', 'inspector', 'usuario'],
            ],
            'permissions.grant' => [
                'display_name' => 'Otorgar permisos',
                'description' => 'Autoriza asignar permisos directos a usuarios.',
                'category' => 'permissions',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'permissions.revoke' => [
                'display_name' => 'Revocar permisos',
                'description' => 'Permite retirar permisos previamente otorgados.',
                'category' => 'permissions',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'permissions.audit' => [
                'display_name' => 'Auditar permisos',
                'description' => 'Autoriza revisar el historial de uso y cambios de permisos.',
                'category' => 'permissions',
                'requires_context' => true,
                'is_system' => true,
                'roles' => ['administrador'],
            ],
            'admin.access' => [
                'display_name' => 'Acceso a panel global',
                'description' => 'Permite acceder al panel de administracion global.',
                'category' => 'admin',
                'requires_context' => false,
                'is_system' => true,
                'roles' => [],
            ],
            'admin.teams.manage' => [
                'display_name' => 'Gestion global de equipos',
                'description' => 'Autoriza administrar cualquier equipo del sistema.',
                'category' => 'admin',
                'requires_context' => false,
                'is_system' => true,
                'roles' => [],
            ],
            'admin.users.manage' => [
                'display_name' => 'Gestion global de usuarios',
                'description' => 'Permite administrar usuarios a nivel de plataforma.',
                'category' => 'admin',
                'requires_context' => false,
                'is_system' => true,
                'roles' => [],
            ],
            'admin.system.settings' => [
                'display_name' => 'Configuracion del sistema',
                'description' => 'Autoriza modificar ajustes globales.',
                'category' => 'admin',
                'requires_context' => false,
                'is_system' => true,
                'roles' => [],
            ],
            'admin.audit.view' => [
                'display_name' => 'Ver auditoria global',
                'description' => 'Permite consultar los registros de auditoria del sistema.',
                'category' => 'admin',
                'requires_context' => false,
                'is_system' => true,
                'roles' => [],
            ],
        ];
    }

    /**
     * Metadata for the default system roles.
     */
    public static function roles(): array
    {
        return [
            'administrador' => [
                'display_name' => 'Administrador',
                'description' => 'Control total del equipo y de su configuracion diaria.',
            ],
            'inspector' => [
                'display_name' => 'Inspector',
                'description' => 'Acceso de solo lectura orientado a auditorias.',
            ],
            'usuario' => [
                'display_name' => 'Usuario',
                'description' => 'Acceso estandar centrado en el fichaje diario.',
            ],
            'editor' => [
                'display_name' => 'Editor',
                'description' => 'Usuario con capacidad para gestionar anuncios del equipo.',
            ],
        ];
    }

    /**
     * Ensure permissions and role assignments exist for the given team.
     */
    public static function syncTeamRoles(int $teamId, ?int $ownerUserId = null): void
    {
        $timestamp = now();
        $definitions = static::definitions();
        static::syncPermissionDefinitions($definitions, $timestamp);

        $roleIds = [];
        foreach (static::roles() as $roleKey => $roleMeta) {
            $roleIds[$roleKey] = static::upsertRole($teamId, $roleKey, $roleMeta, $timestamp);
        }

        $permissionMap = DB::table('permissions')->pluck('id', 'name')->toArray();
        $permissionsByRole = static::permissionsByRole($definitions);

        foreach ($roleIds as $roleKey => $roleId) {
            $permissionNames = $permissionsByRole[$roleKey] ?? [];
            static::syncRolePermissions($roleId, $permissionNames, $permissionMap, $timestamp);
        }

        static::assignLegacyMembers($teamId, $roleIds, $ownerUserId);
    }

    /**
     * Helper to build the canonical role name.
     */
    public static function roleName(string $roleKey, int $teamId): string
    {
        return 'team_' . $teamId . '_' . $roleKey;
    }

    /**
     * Persist all permission definitions.
     */
    protected static function syncPermissionDefinitions(array $definitions, $timestamp): void
    {
        foreach ($definitions as $name => $definition) {
            $existing = DB::table('permissions')->where('name', $name)->first();

            $payload = [
                'display_name' => $definition['display_name'],
                'description' => $definition['description'],
                'category' => $definition['category'],
                'requires_context' => $definition['requires_context'],
                'is_system' => $definition['is_system'] ?? true,
                'updated_at' => $timestamp,
            ];

            if ($existing) {
                DB::table('permissions')->where('id', $existing->id)->update($payload);
            } else {
                $payload['name'] = $name;
                $payload['created_at'] = $timestamp;
                DB::table('permissions')->insert($payload);
            }
        }
    }

    /**
     * Build a map of permissions keyed by role key.
     */
    protected static function permissionsByRole(array $definitions): array
    {
        $map = [];

        foreach ($definitions as $name => $definition) {
            foreach ($definition['roles'] as $roleKey) {
                $map[$roleKey][] = $name;
            }
        }

        return $map;
    }

    /**
     * Create or update a role for the given team.
     */
    protected static function upsertRole(int $teamId, string $roleKey, array $meta, $timestamp): int
    {
        $name = static::roleName($roleKey, $teamId);

        $existing = DB::table('roles')
            ->where('team_id', $teamId)
            ->where('name', $name)
            ->first();

        $payload = [
            'display_name' => $meta['display_name'],
            'description' => $meta['description'],
            'is_system' => true,
            'updated_at' => $timestamp,
        ];

        if ($existing) {
            DB::table('roles')->where('id', $existing->id)->update($payload);
            return (int) $existing->id;
        }

        $payload['team_id'] = $teamId;
        $payload['name'] = $name;
        $payload['created_at'] = $timestamp;

        return (int) DB::table('roles')->insertGetId($payload);
    }

    /**
     * Attach the expected permission set to the role.
     */
    protected static function syncRolePermissions(int $roleId, array $permissionNames, array $permissionMap, $timestamp): void
    {
        if (empty($permissionNames)) {
            return;
        }

        foreach ($permissionNames as $permissionName) {
            $permissionId = $permissionMap[$permissionName] ?? null;
            if (!$permissionId) {
                continue;
            }

            DB::table('permission_role')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ],
                [
                    'granted_at' => $timestamp,
                ]
            );
        }
    }

    /**
     * Map legacy Jetstream roles to the new custom_role_id column.
     */
    protected static function assignLegacyMembers(int $teamId, array $roleIds, ?int $ownerUserId): void
    {
        $legacyMap = [
            'owner' => 'administrador',
            'admin' => 'administrador',
            'administrador' => 'administrador',
            'user' => 'usuario',
            'member' => 'usuario',
            'usuario' => 'usuario',
            'inspect' => 'inspector',
            'inspector' => 'inspector',
            'editor' => 'editor',
        ];

        $rows = DB::table('team_user')
            ->where('team_id', $teamId)
            ->get(['id', 'user_id', 'role', 'custom_role_id']);

        if ($ownerUserId && !$rows->firstWhere('user_id', $ownerUserId)) {
            DB::table('team_user')->insert([
                'team_id' => $teamId,
                'user_id' => $ownerUserId,
                'role' => 'owner',
                'custom_role_id' => $roleIds['administrador'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $rows = DB::table('team_user')
                ->where('team_id', $teamId)
                ->get(['id', 'user_id', 'role', 'custom_role_id']);
        }

        foreach ($rows as $row) {
            $targetKey = $legacyMap[$row->role] ?? null;

            if (!$targetKey && $ownerUserId && (int) $row->user_id === (int) $ownerUserId) {
                $targetKey = 'administrador';
            }

            if (!$targetKey) {
                continue;
            }

            if (!isset($roleIds[$targetKey])) {
                continue;
            }

            if (!is_null($row->custom_role_id)) {
                continue;
            }

            DB::table('team_user')
                ->where('id', $row->id)
                ->update(['custom_role_id' => $roleIds[$targetKey]]);
        }
    }
}
