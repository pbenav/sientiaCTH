# Análisis: Sistema de Permisos Granulares para sientiaCTH

## 📋 Índice
1. [Situación Actual](#situación-actual)
2. [Limitaciones del Sistema Actual](#limitaciones-del-sistema-actual)
3. [Propuesta: Sistema de Permisos Granulares](#propuesta-sistema-de-permisos-granulares)
4. [Arquitectura Propuesta](#arquitectura-propuesta)
5. [Modelo de Datos](#modelo-de-datos)
6. [Acciones Identificadas](#acciones-identificadas)
7. [Plan de Implementación](#plan-de-implementación)
8. [Impacto y Estimación](#impacto-y-estimación)

---

## 🎯 Situación Actual

### Sistema Base: Laravel Jetstream
La aplicación actualmente utiliza el sistema de roles de Laravel Jetstream con 4 roles predefinidos:

| Rol | Permisos Jetstream | Descripción |
|-----|-------------------|-------------|
| **admin** | create, read, update, delete, manage | Administrador de equipo |
| **user** | read, create, update | Usuario estándar |
| **editor** | read, update | Editor de recursos |
| **inspect** | read | Inspector (solo lectura) |

### Verificaciones de Permisos Actuales

#### 1. **A nivel de Política (Policies)**
```php
// TeamPolicy, HolidayPolicy, EventTypePolicy, TeamAnnouncementPolicy
- view / viewAny
- create
- update
- delete
- addTeamMember
- updateTeamMember
- removeTeamMember
```

#### 2. **Verificaciones Directas en Código**
```php
// Verificaciones comunes encontradas:
- auth()->user()->ownsTeam($team)
- auth()->user()->hasTeamRole($team, 'admin')
- auth()->user()->is_admin (administrador global)
- Gate::allows('update', $model)
- Gate::forUser()->authorize('action', $model)
```

#### 3. **Componentes Livewire con Permisos**
- `AnnouncementManager`: viewAny, create, update, delete
- `HolidayManager`: create, update, delete
- `EventTypeManager`: create, update, delete
- `ClockInDelayManager`: update
- `TimezoneManager`: update
- `WorkCenterManager`: create, update, delete (verificación manual)

---

## ⚠️ Limitaciones del Sistema Actual

### 1. **Granularidad Insuficiente**
- No se puede diferenciar entre "editar propios eventos" vs "editar eventos de otros"
- No hay control sobre acciones específicas como "autorizar eventos", "exportar reportes"
- Imposible crear roles intermedios personalizados

### 2. **Falta de Auditoría**
- No se registra quién otorgó/revocó permisos
- No hay historial de cambios de permisos
- Difícil rastrear acciones por tipo de permiso

### 3. **Rigidez en Roles**
- Roles hardcodeados en `JetstreamServiceProvider`
- No se pueden crear roles personalizados dinámicamente
- Permisos genéricos (`create`, `read`, `update`, `delete`) no reflejan operaciones reales

### 4. **Verificaciones Inconsistentes**
- Mezcla de verificaciones en Policies, vistas y Livewire
- Uso directo de `hasTeamRole()` en lugar de permisos semánticos
- Difícil mantenimiento y extensión

### 5. **Sin Contexto de Recursos**
- No se puede dar permiso "solo para ciertos centros de trabajo"
- No hay permisos basados en propietario de recurso
- Falta contexto temporal (permisos por periodo)

---

## 🚀 Propuesta: Sistema de Permisos Granulares

### Objetivos del Nuevo Sistema

1. **Granularidad Total**: Cada acción del sistema tiene su permiso específico
2. **Auditabilidad Completa**: Registro de todas las asignaciones y usos de permisos
3. **Flexibilidad**: Roles dinámicos personalizables por equipo
4. **Contextual**: Permisos con alcance (scope) definido
5. **Escalable**: Fácil agregar nuevos permisos sin modificar código
6. **Compatible**: Mantener compatibilidad con Jetstream mientras se migra

### Características Principales

#### ✅ Permisos Específicos por Dominio
```
events.view.own           - Ver propios eventos
events.view.team          - Ver eventos del equipo
events.create.own         - Crear eventos propios
events.update.own         - Editar propios eventos
events.update.team        - Editar eventos del equipo
events.delete.own         - Eliminar propios eventos
events.delete.team        - Eliminar eventos del equipo
events.authorize          - Autorizar eventos
events.export             - Exportar eventos
```

#### ✅ Roles Dinámicos Personalizables
```php
// Crear roles custom por equipo
$team->roles()->create([
    'name' => 'supervisor_turno',
    'display_name' => 'Supervisor de Turno',
    'description' => 'Gestiona fichajes y autoriza eventos',
]);

// Asignar permisos específicos
$role->permissions()->attach([
    'events.view.team',
    'events.authorize',
    'reports.view.team',
]);
```

#### ✅ Auditoría Automática
```php
// Registro automático de:
- Quién asignó el permiso
- Cuándo se asignó
- A quién se asignó
- Contexto (team, user, reason)
- Cambios (permisos añadidos/removidos)
- Usos del permiso (cada vez que se verifica)
```

#### ✅ Alcance (Scope) de Permisos
```php
// Permisos con contexto
$user->givePermissionTo('events.view.team', [
    'team_id' => 5,
    'work_centers' => [1, 3, 5], // Solo estos centros
    'valid_until' => '2025-12-31', // Temporal
]);
```

---

## 🏗️ Arquitectura Propuesta

### Componentes del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                   CAPA DE APLICACIÓN                         │
│  (Controllers, Livewire, Commands)                          │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ↓
┌─────────────────────────────────────────────────────────────┐
│              CAPA DE AUTORIZACIÓN                            │
│                                                              │
│  ┌────────────┐  ┌────────────┐  ┌──────────────┐         │
│  │   Gates    │  │  Policies  │  │  Middleware  │         │
│  └─────┬──────┘  └─────┬──────┘  └──────┬───────┘         │
│        └────────────────┴────────────────┘                  │
│                         │                                    │
└─────────────────────────┼────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│            SERVICIO DE PERMISOS (PermissionService)          │
│                                                              │
│  - checkPermission(user, permission, context)               │
│  - grantPermission(user, permission, grantedBy, context)    │
│  - revokePermission(user, permission, revokedBy)            │
│  - getUserPermissions(user, team)                           │
│  - auditPermissionUsage(user, permission, action)           │
└─────────────────────────┬────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│                  CAPA DE PERSISTENCIA                        │
│                                                              │
│  ┌──────────┐  ┌────────┐  ┌───────────┐  ┌─────────────┐ │
│  │Permissions│  │ Roles  │  │RoleUser   │  │PermissionLog││
│  └──────────┘  └────────┘  └───────────┘  └─────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Flujo de Verificación

```
Usuario intenta acción
         │
         ↓
    Middleware/Gate verifica
         │
         ↓
    PermissionService.checkPermission()
         │
         ├─→ Cache (si existe y válido)
         │
         ├─→ Base de datos (permisos directos + roles)
         │
         ├─→ Verifica contexto (team, scope, expiración)
         │
         ├─→ Audita uso del permiso
         │
         └─→ Retorna: ALLOWED / DENIED
```

---

## 💾 Modelo de Datos

### Tablas Nuevas

#### 1. `permissions`
```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) UNIQUE NOT NULL,          -- 'events.view.own'
    display_name VARCHAR(191) NOT NULL,          -- 'Ver Propios Eventos'
    description TEXT,
    category VARCHAR(100),                       -- 'events', 'teams', 'reports'
    requires_context BOOLEAN DEFAULT FALSE,      -- Si necesita team_id u otro contexto
    is_system BOOLEAN DEFAULT FALSE,             -- No se puede eliminar
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_category (category)
);
```

#### 2. `roles` (Extendida)
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    display_name VARCHAR(191) NOT NULL,
    description TEXT,
    team_id BIGINT UNSIGNED NULL,                -- NULL = rol global, sino = rol de equipo
    is_system BOOLEAN DEFAULT FALSE,             -- Roles del sistema (admin, user, etc)
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_role_team (name, team_id),
    INDEX idx_team (team_id)
);
```

#### 3. `permission_role` (Pivot)
```sql
CREATE TABLE permission_role (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    granted_by BIGINT UNSIGNED NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_permission_role (permission_id, role_id)
);
```

#### 4. `role_user` (Extendida - ya existe pero se mejora)
```sql
ALTER TABLE team_user 
ADD COLUMN custom_role_id BIGINT UNSIGNED NULL AFTER role,
ADD FOREIGN KEY (custom_role_id) REFERENCES roles(id) ON DELETE SET NULL;

-- El campo 'role' actual de Jetstream se mantiene por compatibilidad
-- custom_role_id apunta al nuevo sistema de roles
```

#### 5. `user_permissions` (Permisos directos)
```sql
CREATE TABLE user_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    team_id BIGINT UNSIGNED NULL,                -- Contexto de equipo
    context JSON NULL,                           -- Contexto adicional (work_centers, etc)
    valid_from TIMESTAMP NULL,
    valid_until TIMESTAMP NULL,
    granted_by BIGINT UNSIGNED NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    revoked_by BIGINT UNSIGNED NULL,
    revoked_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user (user_id),
    INDEX idx_permission (permission_id),
    INDEX idx_team (team_id),
    INDEX idx_active (user_id, valid_until)
);
```

#### 6. `permission_audit_log`
```sql
CREATE TABLE permission_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    permission_name VARCHAR(191) NOT NULL,
    action VARCHAR(50) NOT NULL,                 -- 'granted', 'revoked', 'checked', 'denied'
    result ENUM('allowed', 'denied') NULL,
    performed_by BIGINT UNSIGNED NULL,           -- Quién realizó la acción
    team_id BIGINT UNSIGNED NULL,
    resource_type VARCHAR(100) NULL,             -- Event, Team, Holiday, etc
    resource_id BIGINT UNSIGNED NULL,
    context JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    
    INDEX idx_user (user_id),
    INDEX idx_permission (permission_name),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    INDEX idx_team (team_id)
);
```

---

## 🎬 Acciones Identificadas

### Módulo: EVENTOS (Events)

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `events.view.own` | Ver propios eventos | - |
| `events.view.team` | Ver eventos del equipo | team_id |
| `events.view.all` | Ver todos los eventos (admin global) | - |
| `events.create.own` | Crear eventos propios | team_id |
| `events.create.team` | Crear eventos para otros usuarios | team_id |
| `events.update.own` | Editar propios eventos | - |
| `events.update.team` | Editar eventos del equipo | team_id |
| `events.delete.own` | Eliminar propios eventos | - |
| `events.delete.team` | Eliminar eventos del equipo | team_id |
| `events.authorize` | Autorizar/desautorizar eventos | team_id |
| `events.export` | Exportar eventos | team_id |
| `events.import` | Importar eventos | team_id |
| `events.exceptional.create` | Crear eventos excepcionales | team_id |
| `events.exceptional.approve` | Aprobar eventos excepcionales | team_id |

### Módulo: EQUIPOS (Teams)

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `teams.view` | Ver equipo | team_id |
| `teams.create` | Crear equipos | - |
| `teams.update` | Actualizar configuración | team_id |
| `teams.delete` | Eliminar equipo | team_id |
| `teams.members.view` | Ver miembros | team_id |
| `teams.members.add` | Añadir miembros | team_id |
| `teams.members.remove` | Eliminar miembros | team_id |
| `teams.members.update` | Actualizar roles de miembros | team_id |
| `teams.invitations.send` | Enviar invitaciones | team_id |
| `teams.invitations.cancel` | Cancelar invitaciones | team_id |
| `teams.settings.view` | Ver ajustes | team_id |
| `teams.settings.update` | Actualizar ajustes | team_id |

### Módulo: TIPOS DE EVENTO (Event Types)

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `event_types.view` | Ver tipos de evento | team_id |
| `event_types.create` | Crear tipos de evento | team_id |
| `event_types.update` | Actualizar tipos de evento | team_id |
| `event_types.delete` | Eliminar tipos de evento | team_id |
| `event_types.manage_colors` | Gestionar colores | team_id |

### Módulo: FESTIVOS (Holidays)

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `holidays.view` | Ver festivos | team_id |
| `holidays.create` | Crear festivos | team_id |
| `holidays.update` | Actualizar festivos | team_id |
| `holidays.delete` | Eliminar festivos | team_id |
| `holidays.import` | Importar festivos | team_id |

### Módulo: CENTROS DE TRABAJO (Work Centers)

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `work_centers.view` | Ver centros | team_id |
| `work_centers.create` | Crear centros | team_id |
| `work_centers.update` | Actualizar centros | team_id |
| `work_centers.delete` | Eliminar centros | team_id |

### Módulo: ANUNCIOS (Announcements)

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `announcements.view` | Ver anuncios | team_id |
| `announcements.create` | Crear anuncios | team_id |
| `announcements.update` | Actualizar anuncios | team_id |
| `announcements.delete` | Eliminar anuncios | team_id |
| `announcements.publish` | Publicar/despublicar | team_id |

### Módulo: REPORTES (Reports)

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `reports.view.own` | Ver propios reportes | team_id |
| `reports.view.team` | Ver reportes del equipo | team_id |
| `reports.export` | Exportar reportes | team_id |
| `reports.advanced` | Reportes avanzados | team_id |

### Módulo: USUARIOS (Users)

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `users.view` | Ver usuarios | team_id |
| `users.create` | Crear usuarios | - |
| `users.update` | Actualizar usuarios | team_id |
| `users.delete` | Eliminar usuarios | - |
| `users.impersonate` | Suplantar usuario (admin) | - |
| `users.manage_schedule` | Gestionar horarios | team_id |

### Módulo: ROLES Y PERMISOS

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `roles.view` | Ver roles | team_id |
| `roles.create` | Crear roles | team_id |
| `roles.update` | Actualizar roles | team_id |
| `roles.delete` | Eliminar roles | team_id |
| `roles.assign` | Asignar roles | team_id |
| `permissions.grant` | Otorgar permisos | team_id |
| `permissions.revoke` | Revocar permisos | team_id |
| `permissions.audit` | Ver auditoría de permisos | team_id |

### Módulo: ADMIN GLOBAL

| Permiso | Descripción | Contexto |
|---------|-------------|----------|
| `admin.access` | Acceso panel admin | - |
| `admin.teams.manage` | Gestionar todos los equipos | - |
| `admin.users.manage` | Gestionar todos los usuarios | - |
| `admin.system.settings` | Ajustes del sistema | - |
| `admin.audit.view` | Ver logs de auditoría | - |

---

## 📐 Plan de Implementación

### Fase 1: Fundamentos (Semana 1-2)
**Objetivo**: Crear la base del sistema sin romper funcionalidad existente

#### Tareas:
1. ✅ Crear migraciones de tablas
   - `permissions`
   - Extender `roles`
   - `permission_role`
   - Modificar `team_user`
   - `user_permissions`
   - `permission_audit_log`

2. ✅ Crear modelos Eloquent
   ```php
   - App\Models\Permission
   - App\Models\Role (extender existente)
   - App\Models\UserPermission
   - App\Models\PermissionAuditLog
   ```

3. ✅ Seeders de permisos
   - Crear seeder con todos los permisos identificados
   - Categorizar por módulos
   - Marcar permisos del sistema

4. ✅ Migración de roles Jetstream
   - Convertir roles existentes al nuevo sistema
   - Mantener compatibilidad

#### Entregables:
- Estructura de base de datos completa
- Modelos básicos funcionando
- Permisos semilla cargados

---

### Fase 2: Servicio de Permisos (Semana 3)
**Objetivo**: Implementar lógica de negocio de permisos

#### Tareas:
1. ✅ Crear `PermissionService`
   ```php
   namespace App\Services;
   
   class PermissionService {
       public function checkPermission(User $user, string $permission, array $context = []): bool
       public function grantPermission(User $user, Permission $permission, User $grantedBy, array $context = [])
       public function revokePermission(User $user, Permission $permission, User $revokedBy)
       public function getUserPermissions(User $user, ?Team $team = null): Collection
       public function getRolePermissions(Role $role): Collection
       public function syncRolePermissions(Role $role, array $permissions, User $syncedBy)
   }
   ```

2. ✅ Implementar cache de permisos
   - Cache por usuario + team
   - Invalidación inteligente
   - TTL configurable

3. ✅ Sistema de auditoría
   - Observer para PermissionAuditLog
   - Registrar automáticamente usos
   - Middleware de auditoría

4. ✅ Trait `HasPermissions` para User
   ```php
   trait HasPermissions {
       public function hasPermission(string $permission, array $context = []): bool
       public function givePermissionTo(string|Permission $permission, array $context = [])
       public function removePermissionFrom(string|Permission $permission)
       public function syncPermissions(array $permissions)
       public function getAllPermissions(?Team $team = null): Collection
   }
   ```

#### Entregables:
- `PermissionService` funcional
- Cache implementado
- Trait `HasPermissions` en modelo User
- Auditoría automática

---

### Fase 3: Gates y Policies (Semana 4)
**Objetivo**: Integrar con sistema de autorización de Laravel

#### Tareas:
1. ✅ Actualizar `AuthServiceProvider`
   - Registrar gates dinámicos basados en permisos
   - Mantener gates existentes

2. ✅ Actualizar Policies existentes
   - TeamPolicy
   - EventTypePolicy
   - HolidayPolicy
   - TeamAnnouncementPolicy

3. ✅ Crear `PermissionMiddleware`
   ```php
   Route::middleware(['permission:events.authorize'])->group(...)
   ```

4. ✅ Blade Directives personalizadas
   ```php
   @permission('events.create.own')
   @hasAnyPermission(['events.view.own', 'events.view.team'])
   @role('supervisor_turno')
   ```

#### Entregables:
- Gates dinámicos funcionando
- Policies actualizadas
- Middleware de permisos
- Directivas Blade

---

### Fase 4: UI de Gestión (Semana 5-6)
**Objetivo**: Interfaz para administrar roles y permisos

#### Tareas:
1. ✅ Panel de gestión de roles
   - Livewire: `RoleManager`
   - CRUD completo de roles
   - Asignación de permisos a roles
   - Vista de roles por equipo

2. ✅ Panel de gestión de permisos
   - Livewire: `PermissionManager`
   - Asignación directa de permisos a usuarios
   - Vista jerárquica por módulos
   - Búsqueda y filtrado

3. ✅ Auditoría de permisos
   - Livewire: `PermissionAuditViewer`
   - Timeline de cambios
   - Filtros avanzados
   - Exportación

4. ✅ Integración en Team Settings
   - Pestaña "Roles y Permisos"
   - Asignación masiva
   - Plantillas de roles

#### Entregables:
- UI completa de gestión
- Componentes Livewire probados
- Documentación de uso

---

### Fase 5: Migración Progresiva (Semana 7-8)
**Objetivo**: Migrar código existente al nuevo sistema

#### Estrategia:
Migrar módulo por módulo en orden de prioridad:

1. **Eventos** (más crítico)
   - Sustituir `hasTeamRole()` por `hasPermission()`
   - Actualizar componentes Livewire
   - Probar exhaustivamente

2. **Equipos**
   - Migrar gestión de miembros
   - Actualizar invitaciones

3. **Tipos de Evento, Festivos, Centros**
   - Migración directa
   - Menos riesgo

4. **Reportes y Admin**
   - Últimos en migrar
   - Menos impacto

#### Compatibilidad:
```php
// Mantener compatibilidad durante migración
class User extends Model {
    public function hasTeamRole(Team $team, string $role): bool {
        // Verificar nuevo sistema primero
        if ($this->hasPermission("role.{$role}", ['team_id' => $team->id])) {
            return true;
        }
        
        // Fallback al sistema antiguo
        return $this->belongsToTeam($team) && 
               $this->teamRole($team)->key === $role;
    }
}
```

#### Entregables:
- Código migrado módulo a módulo
- Tests de regresión pasando
- Documentación de cambios

---

### Fase 6: Testing y Optimización (Semana 9)
**Objetivo**: Asegurar calidad y rendimiento

#### Tareas:
1. ✅ Tests unitarios
   - PermissionService
   - Modelos
   - Traits

2. ✅ Tests de feature
   - Flujos completos de autorización
   - Asignación/revocación
   - Auditoría

3. ✅ Tests de rendimiento
   - Benchmarks de verificación
   - Optimización de queries
   - Ajuste de cache

4. ✅ Tests de seguridad
   - Escalada de privilegios
   - Bypass de permisos
   - Inyección de contexto

#### Entregables:
- Suite de tests completa (>80% coverage)
- Benchmarks documentados
- Reporte de seguridad

---

### Fase 7: Documentación y Deploy (Semana 10)
**Objetivo**: Documentar y desplegar en producción

#### Tareas:
1. ✅ Documentación técnica
   - Arquitectura del sistema
   - API reference
   - Ejemplos de uso

2. ✅ Documentación de usuario
   - Guía de gestión de roles
   - Guía de asignación de permisos
   - FAQs

3. ✅ Migración de datos producción
   - Script de migración
   - Backup completo
   - Plan de rollback

4. ✅ Deploy gradual
   - Deploy en staging
   - Testing con usuarios beta
   - Deploy producción

#### Entregables:
- Documentación completa
- Sistema en producción
- Plan de soporte post-deploy

---

## 📊 Impacto y Estimación

### Impacto en el Código

#### Archivos a Modificar
| Tipo | Cantidad Estimada | Descripción |
|------|-------------------|-------------|
| Migraciones | 6 | Nuevas tablas y modificaciones |
| Modelos | 5 | Nuevos + actualización de User |
| Seeders | 1 | Permisos del sistema |
| Services | 1 | PermissionService |
| Policies | 4 | Actualización a nuevo sistema |
| Middleware | 1 | PermissionMiddleware |
| Livewire | 10+ | Actualización de componentes |
| Vistas Blade | 15+ | Actualización de directivas |
| Tests | 20+ | Unitarios + Feature |

#### Código Legacy a Refactorizar
```php
// Buscar y reemplazar (~50 ocurrencias):
hasTeamRole($team, 'admin')  →  hasPermission('teams.update', ['team_id' => $team->id])
ownsTeam($team)              →  hasPermission('teams.manage', ['team_id' => $team->id])
is_admin                     →  hasPermission('admin.access')
```

### Estimación de Tiempo

| Fase | Duración | Esfuerzo (horas) |
|------|----------|------------------|
| Fase 1: Fundamentos | 2 semanas | 40h |
| Fase 2: Servicio | 1 semana | 30h |
| Fase 3: Gates/Policies | 1 semana | 25h |
| Fase 4: UI | 2 semanas | 50h |
| Fase 5: Migración | 2 semanas | 60h |
| Fase 6: Testing | 1 semana | 30h |
| Fase 7: Deploy | 1 semana | 15h |
| **TOTAL** | **10 semanas** | **250h** |

### Riesgos Identificados

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Regresión de funcionalidad | Media | Alto | Tests exhaustivos, deploy gradual |
| Problemas de rendimiento | Baja | Medio | Cache agresivo, índices BD |
| Resistencia de usuarios | Media | Bajo | Documentación, training |
| Complejidad de migración | Alta | Alto | Migración por fases, compatibilidad |
| Bugs en auditoría | Media | Medio | Logging extensivo, monitoreo |

---

## 🎯 Beneficios Esperados

### Para Administradores
- ✅ Control total sobre accesos
- ✅ Roles personalizados por necesidad
- ✅ Auditoría completa de acciones
- ✅ Delegación segura de responsabilidades

### Para Usuarios
- ✅ Claridad en permisos asignados
- ✅ Interfaz intuitiva
- ✅ Menos errores de acceso denegado

### Para el Sistema
- ✅ Código más mantenible
- ✅ Escalabilidad ilimitada
- ✅ Seguridad mejorada
- ✅ Compliance con regulaciones

---

## 📝 Notas Adicionales

### Compatibilidad con Jetstream
El sistema se diseña para ser **compatible** con Jetstream durante la transición:

```php
// Los roles de Jetstream seguirán funcionando
$user->hasTeamRole($team, 'admin'); // ✓ Funciona

// Pero también se puede usar el nuevo sistema
$user->hasPermission('teams.update', ['team_id' => $team->id]); // ✓ Funciona
```

### Extensibilidad Futura
El diseño permite fácilmente:
- Permisos basados en horario
- Permisos basados en ubicación
- Permisos basados en atributos del usuario
- Workflows de aprobación multi-nivel
- Integración con SSO/LDAP

### Performance
Con el sistema de cache y índices adecuados:
- Verificación de permiso: **< 5ms** (cached)
- Verificación de permiso: **< 20ms** (DB)
- Carga de permisos de usuario: **< 50ms**

---

## 🚀 Próximos Pasos Recomendados

1. **Revisión de este documento** con stakeholders
2. **Priorizar permisos** más críticos para Fase 1
3. **Crear branch** `feat/permission` (✅ Ya creado)
4. **Iniciar Fase 1** - Migraciones y modelos
5. **Setup de ambiente de testing** dedicado

---

**Fecha de Análisis**: 7 de diciembre de 2025  
**Autor**: GitHub Copilot  
**Versión**: 1.0  
**Estado**: Propuesta para Revisión

---

## 💖 Apoya el Proyecto

👉 **[Apoyar en Patreon](https://www.patreon.com/cw/sientiaCTH_ControlHorario)**
