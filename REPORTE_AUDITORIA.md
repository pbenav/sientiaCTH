# INFORME DE AUDITORÍA COMPLETA — sientiaCTH v1.0.0

## 1. IDENTIFICACIÓN DEL PROYECTO

| Aspecto | Detalle |
|---------|---------|
| **Nombre** | sientiaCTH |
| **Framework** | Laravel 10 (PHP 8.2) |
| **Autenticación** | Jetstream + Fortify + Sanctum (2FA, equipos) |
| **Frontend** | Blade + Tailwind CSS + Alpine.js + Livewire 3 + FullCalendar |
| **Base de datos** | MySQL (user: `cth`) |
| **Mail** | SMTP (SSL, `info@my.sientia.com`, port 465) |
| **PDF/Excel** | Dompdf, Mpdf, PhpSpreadsheet |
| **Licencia** | AGPL-3.0 |

## 2. ARQUITECTURA Y MÓDULOS

### 2.1 Módulos Principales

| Módulo | Componentes |
|--------|-------------|
| **Time Tracking** | `MobileClockController`, `SmartClockInService`, `PunchController`, `Event` model |
| **Gestión de Equipos** | Jetstream Teams, `TeamController`, `TeamInvitationController` |
| **Eventos (Vacaciones/Ausencias)** | `CreateEventAction`, `AddEvent` (Livewire), `EditEvent` (Livewire) |
| **Mensajería** | `MessageController`, `MessagesComponent` (Livewire) |
| **Informes** | `ReportsController`, Exports (PDF/Excel), `GenerateReportJob` (queue) |
| **Seguridad** | `LoginSecurityService`, `BlockedIpManager`, `PermissionService`, Audit logs |
| **API Móvil** | Routes `/api/v1/mobile/*`, `MobileClockController`, `SyncRequest` |
| **Admin** | `AdminDashboardComponent`, `UserManagementComponent`, `AppSettings`, `MailSettings` |
| **Roles y Permisos** | `RolesPermissionsManager`, `PermissionMatrix`, `PermissionAuditLog` |
| **Kiosk Mode** | `KioskUnlockComponent`, middleware `EnsureNotKioskMode` |

### 2.2 Flujo de Marcaje (Clock-In/Out)

```
Mobile App → MobileClockController::clock()
    ↓
SmartClockInService::getClockAction() → Determina acción (clock_in / clock_out / pause / resume)
    ↓
executeClockAction() → clockIn() / clockOut() / pauseWorkday() / resumeWorkday()
    ↓
Event model → Guardar registro con geolocalización/NFC
    ↓
ClockStatusResource → Respuesta JSON al cliente
```

---

## 3. PROBLEMAS IDENTIFICADOS Y MEJORAS PROPUESTAS

### 🔴 CRÍTICOS (Seguridad)

#### 3.1 Credenciales expuestas en `.env`
**Archivo**: `.env`
**Problema**: Las credenciales de base de datos (`DB_USERNAME=cth`, `DB_PASSWORD=...`) y configuración SMTP están en texto plano y fueron leídas en el contexto.
**Recomendación**: 
- Nunca compartir archivos `.env`. Usar variables de entorno en el servidor.
- Rotar las credenciales inmediatamente si este archivo fue compartido fuera del equipo de desarrollo.
- Implementar `php artisan config:cache` en producción.

#### 3.2 `error_log()` en código de producción
**Archivo**: `app/Models/Event.php` (línea con `error_log('Confirm...');`)
**Problema**: Debug code en producción puede exponer información sensible en los logs del servidor.
**Recomendación**: Reemplazar por `Log::debug()` condicional con `app.debug` o eliminar completamente.

#### 3.3 Consulta directa a tabla `notifications` en `Event::deleted()`
**Archivo**: `app/Models/Event.php`
```php
static::deleted(function ($event) {
    \Illuminate\Support\Facades\DB::table('notifications')
        ->where('data->event_id', $event->id)
        ->delete();
});
```
**Problema**: 
- Consulta SQL directa con JSON path que no usa índices.
- No respeta el soft delete si se implementara.
- Potencial problema de rendimiento al eliminar eventos.
**Recomendación**: Usar el modelo `Notification` con scope, o eliminar las notificaciones como parte de la relación `hasMany` del modelo `Event`.

#### 3.4 `config('app.debug')` usado para logging condicional
**Archivos**: `MobileClockController.php` (múltiples `Log::debug()` condicionales)
**Problema**: Si `debug=true` en producción, se registrarán datos sensibles (request bodies completos) en los logs.
**Recomendación**: Usar un flag de configuración dedicado (`app.debug_api` o `app.mobile_logging`) que pueda controlarse independientemente del debug general.

---

### 🟡 ALTO (Seguridad y Funcionalidad)

#### 3.5 `user_code` auto-generado con race condition
**Archivo**: `app/Models/User.php`
```php
static::creating(function ($user) {
    if (empty($user->user_code)) {
        $nextId = (int) User::max('id') + 1;
        $user->user_code = 'USR' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
    }
});
```
**Problema**: Si dos usuarios se crean simultáneamente, `User::max('id')` puede devolver el mismo valor, generando códigos duplicados.
**Recomendación**: Usar `DB::table('users')->max('id')` dentro de una transacción, o generar el código con un approach atómico (ej. `AUTO_INCREMENT` del DB + trigger, o UUID).

#### 3.6 `transferToTeam()` sin transacción
**Archivo**: `app/Models/User.php`
```php
public function transferToTeam(Team $targetTeam, ?string $role = null): void
{
    $this->teams()->detach();
    $targetTeam->users()->attach($this->id, ['role' => $role]);
    $this->forceFill(['current_team_id' => $targetTeam->id])->save();
    $this->events()->update(['team_id' => $targetTeam->id]);
}
```
**Problema**: Si `events()->update()` falla, el usuario queda sin equipo pero con eventos huérfanos.
**Recomendación**: Envolver en `DB::transaction()`.

#### 3.7 Validación de `currentTeam` nula en múltiples métodos
**Archivos**: `MobileClockController.php`, `User.php`
**Problema**: Métodos como `getWorkerData()`, `status()`, `clock()` acceden a `$user->currentTeam->workCenters()` sin verificar si `currentTeam` es null (aunque `with=['currentTeam']` está en `$with`).
**Recomendación**: Agregar verificación temprana y retorno 400/409 si `currentTeam` es null.

#### 3.8 `getTodayStats()` con lógica de cálculo duplicada y errores
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php`
```php
$endTime = Carbon::parse($event->start);  // Debería ser $event->end
$pauseEnd = Carbon::parse($event->start); // Debería ser $event->end
```
**Problema**: Se parsea `$event->start` en lugar de `$event->end` para calcular el fin del intervalo. Esto genera estadísticas incorrectas.
**Recomendación**: Corregir `$event->end` en ambos lugares.

#### 3.9 `getTodayStats()` llamada duplicada
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php::status()`
```php
$todayStats = $this->getTodayStats($user) ?? [];
$todayStats = $this->getTodayStats($user) ?? []; // Línea duplicada
```
**Problema**: Llamada redundante que consume recursos de base de datos innecesariamente.
**Recomendación**: Eliminar la línea duplicada.

#### 3.10 `status()` llama a `getTodayStats()` dos veces
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php::status()`
**Problema**: Ya se llama en la línea anterior. La asignación `$todayStats = $this->getTodayStats($user) ?? [];` aparece dos veces consecutivas.
**Recomendación**: Eliminar la duplicación.

---

### 🟡 ALTO (Eficiencia y Rendimiento)

#### 3.11 N+1 query en `getWorkerData()`
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php`
```php
foreach ($allTeams as $team) {
    $teamWorkCenters = $team->workCenters;  // N+1
```
**Problema**: Se accede a la relación `workCenters` sin eager loading en un bucle.
**Recomendación**: Usar `$user->ownedTeams()->with('workCenters')->get()` y `$user->teams()->with('workCenters')->get()`.

#### 3.12 `administeredTeamIds()` carga todos los IDs de equipos
**Archivo**: `app/Models/User.php`
```php
public function administeredTeamIds(): array
{
    if ($this->is_admin) {
        return \App\Models\Team::pluck('id')->toArray();
    }
```
**Problema**: Si hay miles de equipos, esto carga todos los IDs en memoria.
**Recomendación**: Usar `pluck('id')->toArray()` con `values()` para limpiar claves, o implementar paginación si es necesario.

#### 3.13 `getPeriod()` y `getPeriodForUser()` sin caching
**Archivo**: `app/Models/Event.php`
**Problema**: Cada llamada carga `eventType` si no está eager-loaded. En listados de múltiples eventos, esto genera N+1.
**Recomendación**: Documentar que se requiere `with('eventType')`, o usar accessors con cache.

#### 3.14 Sync mock sin implementación real
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php::sync()`
```php
$syncResults[] = [
    'event' => $event,
    'success' => true,
    'message' => 'Evento sincronizado (mock)',
    'processed_at' => now()->toISOString()
];
// TODO: implement actual synchronization logic
```
**Problema**: La sincronización offline está completamente sin implementar.
**Recomendación**: Implementar la lógica de creación/actualización de eventos desde el buffer offline, con manejo de conflictos.

---

### 🟢 MEDIO (Mantenibilidad y Código)

#### 3.15 `switchTeam()` duplica lógica de Jetstream
**Archivo**: `app/Models/User.php`
**Problema**: El método sobrescribe el comportamiento de Jetstream pero no delega al trait cuando no es admin. Esto puede desincronizarse con actualizaciones de Jetstream.
**Recomendación**: Documentar el override y asegurar compatibilidad con versiones futuras.

#### 3.16 `hasTeamRole()` con protección PHP 8.4
**Archivo**: `app/Models/User.php`
```php
// Overrides Jetstream's HasTeams trait to prevent null TypeError in PHP 8.4.
```
**Problema**: El override es necesario pero puede perder cambios en actualizaciones de Jetstream.
**Recomendación**: Monitorear cambios en Laravel/Jetstream y ajustar según sea necesario.

#### 3.17 `confirm()` con `error_log()` residual
**Archivo**: `app/Models/Event.php`
```php
public function confirm(): bool
{
    error_log('Confirm...');  // Debug code
```
**Recomendación**: Eliminar el `error_log()`.

#### 3.18 `sync()` con `TODO` sin prioridad
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php`
**Recomendación**: Priorizar la implementación de sincronización offline como feature key para la app móvil.

#### 3.19 `getTodayStats()` calcula `workedSeconds` y `pauseSeconds` de forma ineficiente
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php`
**Problema**: Hace dos pasadas completas sobre todos los eventos del día.
**Recomendación**: Hacer una sola pasada y calcular ambos valores simultáneamente.

#### 3.20 `getPeriodForUser()` cálculo de días hábiles sin cache
**Archivo**: `app/Models/Event.php`
**Problema**: Para eventos de varios días, itera día por día verificando festivos. Sin caché.
**Recomendación**: Implementar caché de festivos por equipo y período.

---

### 🔵 BAJO (Mejoras menores)

#### 3.21 `getWorkerData()` mapea días numéricos a letras
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php`
**Problema**: La conversión de días numéricos a letras (L, M, X, J, V, S, D) se hace en PHP en lugar de almacenarse directamente en el formato correcto.
**Recomendación**: Normalizar el almacenamiento del horario de trabajo en el formato correcto desde el inicio.

#### 3.22 `Holiday` query sin caché
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php`
```php
$holidays = Holiday::where('team_id', $teamId)
    ->whereYear('date', $currentYear)
    ->orderBy('date')
    ->get();
```
**Recomendación**: Implementar caché con `Cache::remember()` para festivos, ya que son datos estáticos anuales.

#### 3.23 `getWorkerData()` no valida que `user_code` coincida con `id` del token
**Problema**: Permite a un usuario obtener sus propios datos pero no verifica que el token Sanctum pertenezca al usuario correcto (aunque `request()->user()` ya lo hace).
**Recomendación**: La validación actual es suficiente, pero agregar comentario explicativo.

#### 3.24 `confirmTeamSwitch()` verifica `belongsToTeam` además de owner
**Archivo**: `app/Http/Controllers/Api/MobileClockController.php`
**Problema**: La lógica de acceso se repite en `confirmTeamSwitch()` y `clock()`.
**Recomendación**: Extraer a un método compartido en `SmartClockInService` o `User`.

---

## 4. MATRIZ DE PRIORIDADES

| Prioridad | Ítems | Acción |
|-----------|-------|--------|
| **Crítica** | 3.1, 3.4 | Rotar credenciales, revisar logs de producción |
| **Alta** | 3.3, 3.5, 3.6, 3.8, 3.9, 3.10 | Fix de bugs funcionales y race conditions |
| **Alta** | 3.7, 3.14 | Null safety y sync offline |
| **Media** | 3.11, 3.12, 3.13, 3.19, 3.20 | Optimización de queries y rendimiento |
| **Media** | 3.15, 3.16, 3.17, 3.18 | Limpieza de código y mantenibilidad |
| **Baja** | 3.21, 3.22, 3.23, 3.24 | Mejoras menores y refactorización |

---

## 5. RECOMENDACIONES GENERALES DE SEGURIDAD

1. **Cifrado de datos sensibles**: Implementar `EncryptionService` existente para datos de geolocalización y NFC.
2. **Rate limiting**: Verificar que las rutas API tengan throttling adecuado (ya existe `throttle:60,1` en `web.php`).
3. **Audit logs**: El sistema de `PermissionAuditLog` es un buen ejemplo. Extenderlo a operaciones de clock-in/out.
4. **CSRF en API**: Las rutas API usan Sanctum (cookie-based CSRF protection). Verificar que los endpoints móviles usen el mismo mecanismo o implementen token validation.
5. **Input sanitization**: El `HtmlSanitizerService` existe. Asegurar que se aplique a todas las entradas de texto libre (observaciones, descripciones).
6. **Session security**: Verificar configuraciones de `session.php` para HTTPS-only y SameSite.

---

*Informe generado: {date}*
*Proyecto: sientiaCTH v1.0.0*
*Framework: Laravel 10 + Jetstream + Livewire 3*
*Base de datos: MySQL*
