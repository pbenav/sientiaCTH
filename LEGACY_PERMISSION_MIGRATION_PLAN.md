# Plan de Migración del Sistema Legacy de Permisos

## Estado Actual

✅ **Sistema híbrido implementado y funcionando:**
- Nuevo sistema granular de permisos (68 permisos, 3 roles por equipo)
- Sistema legacy como fallback (owner/admin/user/inspect)
- 100% de usuarios migrados a roles personalizados
- Todas las verificaciones funcionales pasadas

## Estrategia de Transición

### Fase 1: Despliegue en Producción ✅ LISTA
```bash
# En producción, ejecutar:
php artisan migrate
php artisan permissions:update --force
```

**Verificaciones post-despliegue:**
1. Verificar que todos los usuarios tienen `custom_role_id` asignado
2. Comprobar que los permisos funcionan correctamente
3. Revisar logs de auditoría en `permission_audit_log`

### Fase 2: Período de Estabilidad (1-2 semanas)
**Objetivos:**
- Monitorizar el funcionamiento del nuevo sistema
- Detectar y corregir cualquier problema
- Recoger feedback de usuarios

**Métricas a vigilar:**
- Errores 403 (Unauthorized) no esperados
- Logs de `PermissionAuditLog` con result='denied' inesperados
- Quejas de usuarios sobre permisos

### Fase 3: Migración Gradual del Código (Después de estabilidad)

#### 3.1. Identificar código legacy
Buscar y reemplazar patrones antiguos:

```bash
# Buscar usos del sistema legacy
grep -r "hasTeamRole" app/
grep -r "ownsTeam" app/
grep -r "->role ==" app/
grep -r "team_user.role" app/
```

#### 3.2. Patrones de migración

**Antes (legacy):**
```php
if ($user->hasTeamRole($team, 'admin')) {
    // ...
}

if ($user->ownsTeam($team)) {
    // ...
}

if (auth()->user()->is_admin) {
    // ...
}
```

**Después (nuevo sistema):**
```php
if ($user->can('teams.manage_members', $team)) {
    // ...
}

if ($user->can('teams.transfer_ownership', $team)) {
    // ...
}

if (auth()->user()->can('admin.access_all')) {
    // ...
}
```

#### 3.3. Migrar código crítico primero
1. **Gestión de eventos** (eventos.create.*, eventos.update.*, etc.)
2. **Gestión de equipos** (teams.manage_members, teams.update, etc.)
3. **Gestión de usuarios** (users.create, users.update, users.delete)
4. **Administración** (roles.*, permissions.*)

### Fase 4: Eliminación del Sistema Legacy

Una vez que:
- ✅ El sistema lleve 1-2 semanas estable en producción
- ✅ Todo el código legacy esté migrado
- ✅ No haya referencias a `hasTeamRole()`, `ownsTeam()`, etc.

**Eliminar:**

1. **Método `checkLegacyPermission()` en `PermissionService`:**
```php
// En app/Services/PermissionService.php
// Eliminar todo el método checkLegacyPermission()
// Eliminar las llamadas a este método (líneas 56 y 103)
```

2. **Columna `role` en tabla `team_user`:**
```bash
php artisan make:migration remove_legacy_role_from_team_user
```

```php
public function up()
{
    Schema::table('team_user', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
```

3. **Métodos legacy en modelos:**
- `User::hasTeamRole()`
- `User::ownsTeam()`
- Referencias a `team_user.role`

## Checklist Final

Antes de eliminar el sistema legacy, verificar:

- [ ] Sistema estable en producción durante 2+ semanas
- [ ] Cero errores relacionados con permisos
- [ ] Todo el código migrado (grep devuelve 0 resultados)
- [ ] Tests actualizados
- [ ] Documentación actualizada
- [ ] Equipo informado del cambio

## Notas Importantes

⚠️ **NO ELIMINAR EL SISTEMA LEGACY HASTA:**
1. Confirmar estabilidad en producción
2. Tener backup completo de la base de datos
3. Migrar TODO el código que depende de él
4. Hacer un release note informando del cambio

💡 **El sistema legacy actúa como red de seguridad durante la transición.**

## Contacto
Para dudas sobre la migración, revisar:
- `app/Services/PermissionService.php` (implementación híbrida)
- `database/seeders/SystemRolesSeeder.php` (migración de usuarios)
- `app/Console/Commands/UpdatePermissions.php` (comando de actualización)
