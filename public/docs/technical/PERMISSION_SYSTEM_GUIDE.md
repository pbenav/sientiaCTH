# Sistema de Permisos Granulares - Guía de Uso

## Descripción General

El sistema implementa un **modelo híbrido** que combina:
1. **Nuevo sistema de permisos granulares** (68 permisos específicos)
2. **Sistema legacy** basado en roles (`owner`, `admin`, `member`, `inspect`)
3. **Flag `is_admin`** para administrador global

Durante la transición, ambos sistemas funcionan en paralelo. El código nuevo debe usar el sistema granular, mientras el código antiguo seguirá funcionando con roles legacy.

## Uso en Código PHP

### Modelo User

```php
// Verificar un permiso
if ($user->can('events.create.team')) {
    // Usuario puede crear eventos para otros del equipo
}

// Verificar con contexto de equipo específico
if ($user->can('events.view.team', $team)) {
    // Usuario puede ver eventos del equipo especificado
}

// Verificar permiso negado
if ($user->cannot('teams.delete')) {
    // Usuario NO puede eliminar equipos
}

// Verificar cualquiera de múltiples permisos
if ($user->hasAnyPermission(['events.create.own', 'events.create.team'])) {
    // Usuario puede crear eventos (propios o de otros)
}

// Verificar todos los permisos requeridos
if ($user->hasAllPermissions(['events.view.team', 'events.update.team'])) {
    // Usuario puede ver Y editar eventos del equipo
}
```

### Helpers Globales

```php
// Verificar permiso del usuario autenticado
if (userCan('events.delete.team')) {
    // Hacer algo
}

// Verificar permiso negado
if (userCannot('admin.access')) {
    // Mostrar mensaje de error
}

// Múltiples permisos
if (userHasAnyPermission(['roles.create', 'roles.update'])) {
    // Usuario puede gestionar roles
}
```

## Uso en Vistas Blade

### Directivas Personalizadas

```blade
@canPermission('events.create.team')
    <button>Crear Evento para Otros</button>
@endcanPermission

@cannotPermission('teams.delete')
    <p class="text-gray-500">No tienes permiso para eliminar equipos</p>
@endcannotPermission

@hasAnyPermission(['announcements.create', 'announcements.update'])
    <a href="{{ route('announcements.manage') }}">Gestionar Anuncios</a>
@endhasAnyPermission

@hasAllPermissions(['users.view', 'users.manage_permissions'])
    <button>Gestionar Permisos de Usuario</button>
@endhasAllPermissions
```

### Helpers en Blade

```blade
@if(userCan('events.export'))
    <button wire:click="export">Exportar</button>
@endif

@if(userCannot('teams.transfer_ownership'))
    <span class="text-sm text-gray-500">Solo el propietario puede transferir</span>
@endif
```

## Permisos Disponibles

### Eventos (14 permisos)
- `events.view.own` - Ver propios eventos
- `events.view.team` - Ver eventos del equipo
- `events.view.all` - Ver todos los eventos (admin)
- `events.create.own` - Crear eventos propios
- `events.create.team` - Crear eventos para otros
- `events.update.own` - Editar propios eventos
- `events.update.team` - Editar eventos del equipo
- `events.delete.own` - Eliminar propios eventos
- `events.delete.team` - Eliminar eventos del equipo
- `events.authorize` - Autorizar eventos
- `events.close` - Cerrar eventos
- `events.export` - Exportar eventos
- `events.import` - Importar eventos
- `events.history` - Ver historial de eventos

### Equipos (12 permisos)
- `teams.view` - Ver equipos
- `teams.create` - Crear equipos
- `teams.update` - Editar equipos
- `teams.delete` - Eliminar equipos
- `teams.manage_members` - Gestionar miembros
- `teams.manage_roles` - Gestionar roles de miembros
- `teams.invite` - Invitar usuarios
- `teams.transfer_ownership` - Transferir propiedad
- `teams.view_settings` - Ver configuración
- `teams.update_settings` - Editar configuración
- `teams.manage_work_centers` - Gestionar centros de trabajo
- `teams.switch` - Cambiar de equipo

### Usuarios (6 permisos)
- `users.view` - Ver usuarios
- `users.create` - Crear usuarios
- `users.update` - Editar usuarios
- `users.delete` - Eliminar usuarios
- `users.impersonate` - Suplantar usuarios
- `users.manage_permissions` - Gestionar permisos de usuario

### Roles (5 permisos)
- `roles.view` - Ver roles
- `roles.create` - Crear roles
- `roles.update` - Editar roles
- `roles.delete` - Eliminar roles
- `roles.assign` - Asignar roles

### Administración (5 permisos)
- `admin.access` - Acceder a admin
- `admin.settings` - Configuración del sistema
- `admin.audit` - Ver auditoría
- `admin.mail` - Configurar correo
- `admin.teams` - Gestionar todos los equipos

Y más categorías: `event_types`, `holidays`, `work_centers`, `announcements`, `reports`, `permissions`

## Mapeo Legacy → Granular

El sistema automáticamente mapea roles legacy a permisos:

### Owner
- Tiene **TODOS** los permisos

### Admin
- Tiene todos excepto:
  - `teams.transfer_ownership`
  - `teams.delete`

### Member
- Solo permisos de lectura y gestión propia:
  - `events.view.own`, `events.view.team`, `events.create.own`
  - `events.update.own`, `events.delete.own`
  - Ver configuración y recursos del equipo
  - Generar reportes propios

### Inspector
- Solo permisos de **lectura** (`.view`)

## Gestión de Permisos

### Comando de Actualización

```bash
# Actualizar permisos del sistema
php artisan permissions:update

# Ver cambios sin aplicar
php artisan permissions:update --dry-run

# Forzar sin confirmación
php artisan permissions:update --force
```

Este comando:
1. Actualiza/crea permisos del sistema
2. Preserva permisos personalizados
3. Asigna permisos a roles existentes

### Asignar Permiso Directo a Usuario

```php
$user->givePermissionTo('events.authorize', [
    'team_id' => $team->id,
    'valid_from' => now(),
    'valid_until' => now()->addMonths(6),
]);
```

### Revocar Permiso

```php
$user->revokePermissionTo('events.authorize');
```

## Orden de Prioridad

El sistema verifica permisos en este orden:

1. **is_admin global** → Acceso total (bypass)
2. **Permisos directos del usuario** → Permisos específicos asignados
3. **Rol personalizado** (custom_role_id en team_user) → Permisos del rol
4. **Rol legacy** (role en team_user) → Mapeo automático a permisos

## Migración del Código Antiguo

### Antes (legacy)
```php
if ($user->isTeamAdmin($team)) {
    // Hacer algo
}
```

### Después (granular)
```php
if ($user->can('teams.update', $team)) {
    // Hacer algo
}
```

### En Blade - Antes
```blade
@if(Auth::user()->is_admin || Auth::user()->ownsTeam($team) || Auth::user()->hasTeamRole($team, 'admin'))
    <button>Gestionar</button>
@endif
```

### En Blade - Después
```blade
@canPermission('teams.manage_members')
    <button>Gestionar</button>
@endcanPermission
```

## Cache

Los permisos se cachean durante 60 segundos para optimizar rendimiento. El cache se limpia automáticamente al:
- Asignar un permiso
- Revocar un permiso
- Actualizar roles

## Auditoría

Todos los checks y cambios de permisos se registran en `permission_audit_log`:
- Qué permiso se verificó
- Resultado (permitido/denegado)
- Usuario que lo ejecutó
- Fecha y hora
- Contexto (equipo, etc.)

## Ejemplo Completo

```php
// Controlador
public function createEvent(Request $request, Team $team)
{
    // Verificar permiso
    if ($request->user()->cannot('events.create.team', $team)) {
        abort(403, 'No tienes permiso para crear eventos para otros usuarios');
    }
    
    // Crear evento...
}
```

```blade
<!-- Vista -->
@canPermission('events.create.team')
    <form wire:submit.prevent="createEvent">
        <select name="user_id">
            <!-- Selector de usuario -->
        </select>
        <button type="submit">Crear Evento</button>
    </form>
@else
    <p class="text-gray-500">Solo puedes crear eventos para ti mismo</p>
    <a href="{{ route('events.create.own') }}">Crear mi evento</a>
@endcanPermission
```

## Despliegue a Producción

```bash
git pull
composer install --no-dev
composer dump-autoload
php artisan migrate
php artisan permissions:update --force
php artisan optimize
```
