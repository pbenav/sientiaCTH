# 🔒 Auditoría de Seguridad - Sistema de Anuncios de Equipo

**Fecha:** 3 de noviembre de 2025  
**Rama:** feature-team-announcements  
**Auditor:** GitHub Copilot  

---

## 📋 Resumen Ejecutivo

El sistema de anuncios de equipo ha sido auditado exhaustivamente. Se identificaron **3 vulnerabilidades críticas** que requieren corrección inmediata y **2 mejoras de seguridad recomendadas**.

**Estado General:** ⚠️ REQUIERE CORRECCIONES

---

## 🚨 VULNERABILIDADES CRÍTICAS

### 1. ❌ FALTA DE AUTORIZACIÓN EN COMPONENTE LIVEWIRE

**Severidad:** CRÍTICA  
**Archivo:** `app/Http/Livewire/Teams/AnnouncementManager.php`  
**Líneas:** 51-54, 67-97, 101-107, 113-117

**Problema:**
```php
public function edit(int $id)
{
    $announcement = TeamAnnouncement::findOrFail($id);
    // ❌ NO verifica que el usuario tenga permisos sobre este equipo
    $this->editingId = $announcement->id;
    // ...
}

public function delete(int $id)
{
    $announcement = TeamAnnouncement::findOrFail($id);
    // ❌ NO verifica permisos
    $announcement->delete();
}

public function toggleActive(int $id)
{
    $announcement = TeamAnnouncement::findOrFail($id);
    // ❌ NO verifica permisos
    $announcement->update(['is_active' => !$announcement->is_active]);
}
```

**Riesgo:**
- Un usuario puede editar/eliminar anuncios de **cualquier equipo** conociendo el ID
- Vulnerabilidad de **Insecure Direct Object Reference (IDOR)**
- Posible **escalación de privilegios**

**Impacto:** Un usuario malicioso puede:
1. Enumerar IDs y modificar anuncios de otros equipos
2. Eliminar anuncios críticos
3. Activar/desactivar anuncios sin autorización

**Solución Requerida:**
```php
public function edit(int $id)
{
    $announcement = TeamAnnouncement::findOrFail($id);
    
    // ✅ Verificar que el anuncio pertenece al equipo del usuario
    if ($announcement->team_id !== $this->team->id) {
        abort(403, 'Unauthorized action.');
    }
    
    // ✅ O usar Gate/Policy
    $this->authorize('update', $announcement);
    
    $this->editingId = $announcement->id;
    // ...
}
```

---

### 2. ❌ FALTA DE POLÍTICA DE AUTORIZACIÓN (POLICY)

**Severidad:** CRÍTICA  
**Archivos faltantes:** 
- `app/Policies/TeamAnnouncementPolicy.php` (NO EXISTE)
- Registro en `AuthServiceProvider.php`

**Problema:**
No existe ninguna política formal que defina:
- Quién puede crear anuncios
- Quién puede editar anuncios
- Quién puede eliminar anuncios
- Quién puede ver anuncios

**Riesgo:**
- Cualquier usuario autenticado podría acceder al componente
- No hay control granular de permisos
- Falta de trazabilidad en decisiones de autorización

**Solución Requerida:**
Crear `TeamAnnouncementPolicy.php` con:
```php
public function viewAny(User $user, Team $team): bool
{
    return $user->belongsToTeam($team);
}

public function create(User $user, Team $team): bool
{
    return $user->hasTeamPermission($team, 'announcement:create') 
        || $user->ownsTeam($team);
}

public function update(User $user, TeamAnnouncement $announcement): bool
{
    return ($user->hasTeamPermission($announcement->team, 'announcement:update') 
        || $user->ownsTeam($announcement->team))
        && $user->belongsToTeam($announcement->team);
}

public function delete(User $user, TeamAnnouncement $announcement): bool
{
    return ($user->hasTeamPermission($announcement->team, 'announcement:delete') 
        || $user->ownsTeam($announcement->team))
        && $user->belongsToTeam($announcement->team);
}
```

---

### 3. ❌ FALTA DE VALIDACIÓN DE PROPIEDAD DEL EQUIPO

**Severidad:** CRÍTICA  
**Archivo:** `app/Http/Livewire/Teams/AnnouncementManager.php`  
**Línea:** 36

**Problema:**
```php
public function mount(Team $team)
{
    $this->team = $team;
    // ❌ NO verifica que el usuario pertenezca a este equipo
}
```

**Riesgo:**
- Un usuario puede acceder a la gestión de anuncios de cualquier equipo
- Solo necesita conocer/enumerar IDs de equipos
- Bypass completo de seguridad a nivel de componente

**Prueba de concepto:**
```
GET /teams/999/settings?tab=announcements
// Usuario no pertenece al equipo 999 pero puede acceder
```

**Solución Requerida:**
```php
public function mount(Team $team)
{
    // ✅ Verificar pertenencia al equipo
    if (!auth()->user()->belongsToTeam($team)) {
        abort(403, 'You do not belong to this team.');
    }
    
    // ✅ Verificar permisos de gestión
    if (!auth()->user()->hasTeamPermission($team, 'team:update')) {
        abort(403, 'You do not have permission to manage announcements.');
    }
    
    $this->team = $team;
}
```

---

## ⚠️ VULNERABILIDADES DE SEVERIDAD MEDIA

### 4. ⚠️ MASS ASSIGNMENT EN MODELO

**Severidad:** MEDIA  
**Archivo:** `app/Models/TeamAnnouncement.php`  
**Líneas:** 21-29

**Problema:**
```php
protected $fillable = [
    'team_id',        // ⚠️ Puede ser modificado en update
    'title',
    'content',
    'is_active',
    'start_date',
    'end_date',
    'created_by',     // ⚠️ Puede ser suplantado
];
```

**Riesgo:**
Si hay un bug en la validación, un atacante podría:
- Cambiar el `team_id` de un anuncio (moverlo a otro equipo)
- Suplantar el `created_by` para falsificar autoría

**Solución Recomendada:**
```php
protected $fillable = [
    'title',
    'content',
    'is_active',
    'start_date',
    'end_date',
];

protected $guarded = [
    'team_id',      // ✅ Nunca debe ser modificado después de crear
    'created_by',   // ✅ Solo se asigna al crear
];
```

Y en el componente:
```php
public function save()
{
    // ...
    if ($this->editingId) {
        $announcement = TeamAnnouncement::findOrFail($this->editingId);
        // ✅ NO incluir team_id ni created_by en update
        $announcement->update([
            'title' => $this->title,
            'content' => $sanitizedContent,
            'is_active' => $this->is_active,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
        ]);
    }
}
```

---

### 5. ⚠️ LÍMITES DE TASA (RATE LIMITING)

**Severidad:** MEDIA  
**Archivos:** Componentes Livewire

**Problema:**
No hay límite de tasa para operaciones CRUD de anuncios.

**Riesgo:**
- Spam de anuncios
- Ataque de denegación de servicio (DoS)
- Sobrecarga de base de datos

**Solución Recomendada:**
Agregar throttling en el componente:
```php
use Illuminate\Support\Facades\RateLimiter;

public function save()
{
    $key = 'announcement-save:' . auth()->id();
    
    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        session()->flash('error', "Too many attempts. Try again in {$seconds} seconds.");
        return;
    }
    
    RateLimiter::hit($key, 60); // 5 intentos por minuto
    
    // ... resto del código
}
```

---

## ✅ CONTROLES DE SEGURIDAD IMPLEMENTADOS

### 1. ✅ SANITIZACIÓN HTML (HTMLPurifier)

**Estado:** IMPLEMENTADO CORRECTAMENTE  
**Archivo:** `app/Services/HtmlSanitizerService.php`

**Protecciones:**
- ✅ Eliminación de scripts maliciosos
- ✅ Eliminación de eventos JavaScript (onclick, onerror, etc.)
- ✅ Eliminación de iframes
- ✅ Validación de URIs (bloquea javascript:)
- ✅ Whitelist de etiquetas HTML seguras
- ✅ Whitelist de atributos CSS seguros

**Validado con tests:**
```
✅ Script tags eliminados
✅ Onclick events eliminados
✅ Onerror events eliminados
✅ javascript: URLs eliminados
✅ Iframes bloqueados
✅ HTML seguro preservado
```

---

### 2. ✅ VALIDACIÓN DE ENTRADA

**Estado:** IMPLEMENTADO CORRECTAMENTE  
**Archivo:** `app/Http/Livewire/Teams/AnnouncementManager.php`

**Validaciones:**
```php
protected $rules = [
    'title' => 'required|string|max:255',      // ✅ Límite de longitud
    'content' => 'required|string',             // ✅ Tipo validado
    'is_active' => 'boolean',                   // ✅ Tipo booleano
    'start_date' => 'nullable|date',            // ✅ Formato de fecha
    'end_date' => 'nullable|date|after_or_equal:start_date', // ✅ Lógica de fechas
];
```

---

### 3. ✅ PROTECCIÓN CSRF

**Estado:** IMPLEMENTADO AUTOMÁTICAMENTE  
**Framework:** Laravel + Livewire

Livewire incluye protección CSRF automática en todas las peticiones.

---

### 4. ✅ FOREIGN KEY CONSTRAINTS

**Estado:** IMPLEMENTADO CORRECTAMENTE  
**Archivo:** Migration `create_team_announcements_table.php`

```php
$table->foreignId('team_id')->constrained()->onDelete('cascade');
$table->foreignId('created_by')->constrained('users')->onDelete('cascade');
```

**Protecciones:**
- ✅ Integridad referencial garantizada
- ✅ Limpieza automática al eliminar equipo/usuario
- ✅ Prevención de registros huérfanos

---

## 🔍 VECTORES DE ATAQUE ANALIZADOS

| Vector | Estado | Protección |
|--------|--------|------------|
| XSS (Cross-Site Scripting) | ✅ PROTEGIDO | HTMLPurifier sanitiza todo HTML |
| CSRF (Cross-Site Request Forgery) | ✅ PROTEGIDO | Laravel CSRF tokens |
| SQL Injection | ✅ PROTEGIDO | Eloquent ORM con prepared statements |
| IDOR (Insecure Direct Object Reference) | ❌ VULNERABLE | Falta verificación de permisos |
| Mass Assignment | ⚠️ RIESGO MEDIO | Fillable permite team_id y created_by |
| Authorization Bypass | ❌ VULNERABLE | Sin Policy ni verificación en mount() |
| DoS via Rate Limiting | ⚠️ RIESGO MEDIO | Sin límites de tasa |
| HTML Injection | ✅ PROTEGIDO | HTMLPurifier con whitelist estricta |
| Path Traversal | ✅ N/A | No hay operaciones de archivos |
| Session Hijacking | ✅ PROTEGIDO | Laravel session security |

---

## 📝 RECOMENDACIONES PRIORITARIAS

### Prioridad 1 (CRÍTICO - Implementar INMEDIATAMENTE)

1. **Crear TeamAnnouncementPolicy**
   - Definir permisos granulares
   - Registrar en AuthServiceProvider
   - Aplicar con `$this->authorize()`

2. **Agregar verificación de equipo en mount()**
   - Verificar `belongsToTeam()`
   - Verificar permisos de gestión
   - Abortar con 403 si no autorizado

3. **Verificar ownership en CRUD operations**
   - `edit()`: verificar que anuncio pertenece al equipo
   - `delete()`: verificar permisos
   - `toggleActive()`: verificar permisos

### Prioridad 2 (ALTA - Implementar esta semana)

4. **Proteger campos sensibles en modelo**
   - Mover `team_id` y `created_by` a `$guarded`
   - Actualizar lógica de save() para no incluirlos en update

5. **Agregar Rate Limiting**
   - Limitar creación/edición de anuncios
   - Prevenir spam y abuso

### Prioridad 3 (MEDIA - Mejoras futuras)

6. **Logging y Auditoría**
   - Log de creación/edición/eliminación de anuncios
   - Incluir IP y user agent
   - Facilitar investigación de incidentes

7. **Tests de Seguridad**
   - Unit tests para Policy
   - Feature tests para IDOR
   - Integration tests para sanitización

---

## 🛡️ CÓDIGO DE CORRECCIÓN SUGERIDO

### Archivo: `app/Policies/TeamAnnouncementPolicy.php` (NUEVO)

```php
<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\TeamAnnouncement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamAnnouncementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any announcements.
     */
    public function viewAny(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine if the user can view the announcement.
     */
    public function view(User $user, TeamAnnouncement $announcement): bool
    {
        return $user->belongsToTeam($announcement->team);
    }

    /**
     * Determine if the user can create announcements.
     */
    public function create(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, 'team:update') 
            || $user->ownsTeam($team);
    }

    /**
     * Determine if the user can update the announcement.
     */
    public function update(User $user, TeamAnnouncement $announcement): bool
    {
        return $user->belongsToTeam($announcement->team)
            && ($user->hasTeamPermission($announcement->team, 'team:update') 
                || $user->ownsTeam($announcement->team));
    }

    /**
     * Determine if the user can delete the announcement.
     */
    public function delete(User $user, TeamAnnouncement $announcement): bool
    {
        return $user->belongsToTeam($announcement->team)
            && ($user->hasTeamPermission($announcement->team, 'team:update') 
                || $user->ownsTeam($announcement->team));
    }
}
```

### Actualización: `app/Http/Livewire/Teams/AnnouncementManager.php`

```php
public function mount(Team $team)
{
    // Verificar autorización
    $this->authorize('viewAny', [TeamAnnouncement::class, $team]);
    
    // Verificar que el usuario puede gestionar el equipo
    if (!auth()->user()->hasTeamPermission($team, 'team:update') 
        && !auth()->user()->ownsTeam($team)) {
        abort(403, 'You do not have permission to manage announcements.');
    }
    
    $this->team = $team;
}

public function edit(int $id)
{
    $announcement = TeamAnnouncement::findOrFail($id);
    
    // Verificar autorización
    $this->authorize('update', $announcement);
    
    // Verificar que pertenece al equipo actual
    if ($announcement->team_id !== $this->team->id) {
        abort(403, 'Unauthorized action.');
    }
    
    $this->editingId = $announcement->id;
    // ... resto del código
}

public function delete(int $id)
{
    $announcement = TeamAnnouncement::findOrFail($id);
    
    // Verificar autorización
    $this->authorize('delete', $announcement);
    
    // Verificar que pertenece al equipo actual
    if ($announcement->team_id !== $this->team->id) {
        abort(403, 'Unauthorized action.');
    }
    
    $announcement->delete();
    session()->flash('message', __('Announcement deleted successfully.'));
}

public function toggleActive(int $id)
{
    $announcement = TeamAnnouncement::findOrFail($id);
    
    // Verificar autorización
    $this->authorize('update', $announcement);
    
    // Verificar que pertenece al equipo actual
    if ($announcement->team_id !== $this->team->id) {
        abort(403, 'Unauthorized action.');
    }
    
    $announcement->update(['is_active' => !$announcement->is_active]);
}
```

### Actualización: `app/Models/TeamAnnouncement.php`

```php
protected $fillable = [
    'title',
    'content',
    'is_active',
    'start_date',
    'end_date',
];

protected $guarded = [
    'id',
    'team_id',
    'created_by',
    'created_at',
    'updated_at',
];
```

### Registro: `app/Providers/AuthServiceProvider.php`

```php
use App\Models\TeamAnnouncement;
use App\Policies\TeamAnnouncementPolicy;

protected $policies = [
    // ... otras políticas
    TeamAnnouncement::class => TeamAnnouncementPolicy::class,
];
```

---

## 📊 RESUMEN DE HALLAZGOS

| Severidad | Cantidad | Estado |
|-----------|----------|--------|
| 🔴 Crítica | 3 | ❌ Requiere corrección |
| 🟠 Alta | 0 | - |
| 🟡 Media | 2 | ⚠️ Recomendado |
| 🔵 Baja | 0 | - |
| ✅ Correctos | 4 | ✅ Implementados |

---

## 🎯 CONCLUSIONES

El sistema tiene una **base sólida de seguridad** con sanitización HTML implementada correctamente. Sin embargo, presenta **vulnerabilidades críticas de autorización** que deben ser corregidas antes de desplegar a producción.

**No se recomienda el despliegue hasta que se implementen las correcciones de Prioridad 1.**

---

## ✍️ FIRMA

**Auditor:** GitHub Copilot  
**Fecha:** 3 de noviembre de 2025  
**Versión del documento:** 1.0
