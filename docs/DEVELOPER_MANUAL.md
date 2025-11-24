# 📚 Manual del Desarrollador - CTH (Control de Tiempo y Horarios)

**Versión**: 2025.11  
**Última actualización**: Noviembre 2025  
**Estado**: ✅ Producción

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Configuración y Despliegue](#configuración-y-despliegue)
4. [Funcionalidades Principales](#funcionalidades-principales)
5. [API y Endpoints](#api-y-endpoints)
6. [Aplicación Móvil Flutter](#aplicación-móvil-flutter)
7. [Comandos de Consola](#comandos-de-consola)
8. [Seguridad](#seguridad)
9. [Testing y QA](#testing-y-qa)
10. [Mantenimiento y Troubleshooting](#mantenimiento-y-troubleshooting)

---

## 🎯 Introducción

CTH (Control de Tiempo y Horarios) es un sistema integral de gestión de fichajes y control horario desarrollado con Laravel (backend) y Flutter (aplicación móvil). El sistema permite a los trabajadores registrar su entrada, pausas y salida, mientras proporciona a los administradores herramientas completas de gestión y reporting.

### Componentes del Sistema

- **Backend**: Laravel 9+ con Livewire
- **Frontend Web**: Blade templates + Tailwind CSS + Alpine.js
- **Aplicación Móvil**: Flutter 3.16+ (Android)
- **Base de Datos**: MySQL/MariaDB
- **Autenticación**: Laravel Jetstream

---

## 🏗️ Arquitectura del Sistema

### Stack Tecnológico

#### Backend (Laravel)
```
Laravel 9+
├── Livewire (componentes reactivos)
├── Jetstream (autenticación y equipos)
├── Tailwind CSS (estilos)
├── Alpine.js (interactividad)
└── MySQL/MariaDB (base de datos)
```

#### Frontend Móvil (Flutter)
```
Flutter 3.16+
├── Provider/Riverpod (state management)
├── Dio (HTTP client)
├── Hive/SQLite (almacenamiento local)
├── QR Code Scanner (lectura QR/NFC)
└── Geolocator (geolocalización)
```

### Estructura de Directorios (Laravel)

```
cth/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Livewire/          # Componentes Livewire
│   ├── Models/                # Modelos Eloquent
│   ├── Services/              # Lógica de negocio
│   │   └── SmartClockInService.php
│   └── Console/
│       └── Commands/          # Comandos Artisan
├── database/
│   ├── migrations/            # Migraciones de BD
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── livewire/         # Vistas Livewire
│   │   └── layouts/
│   └── lang/                  # Traducciones
├── routes/
│   ├── web.php
│   └── api.php               # Rutas API móvil
├── public/
└── docs/                     # Documentación
```

### Modelos Principales

#### User (Usuario)
- Trabajador del sistema
- Pertenece a un equipo (Team)
- Tiene código secreto para fichaje móvil
- Asociado a horarios y eventos

#### Team (Equipo)
- Organización o empresa
- Contiene usuarios y centros de trabajo
- Configuración de tipos de evento
- Configuración de anuncios

#### WorkCenter (Centro de Trabajo)
- Ubicación física de trabajo
- Soporte para NFC/QR
- Asociado a un equipo

#### Event (Evento)
- Registro de fichaje
- Tipos: Jornada laboral, Pausa, Vacaciones, etc.
- Campos: start, end, event_type_id, user_id
- Flags: is_open, is_extra_hours

#### EventType (Tipo de Evento)
- Define categorías de eventos
- Configuración por equipo
- Flags: is_workday_type, is_break_type
- Límites de duración

---

## ⚙️ Configuración y Despliegue

### Requisitos del Sistema

- **PHP**: 8.1+
- **Composer**: 2.x
- **Node.js**: 16+ (para compilar assets)
- **MySQL/MariaDB**: 5.7+ / 10.3+
- **Servidor Web**: Apache/Nginx

### Instalación Inicial

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-org/cth.git
cd cth

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias Node
npm install

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar base de datos en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cth
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password

# 6. Ejecutar migraciones
php artisan migrate

# 7. Compilar assets
npm run dev  # Desarrollo
npm run build  # Producción

# 8. Iniciar servidor
php artisan serve
```

### Despliegue en Producción

```bash
# 1. Actualizar código
git pull origin main

# 2. Actualizar dependencias
composer install --optimize-autoloader --no-dev

# 3. Ejecutar migraciones
php artisan migrate --force

# 4. Limpiar y optimizar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Compilar assets para producción
npm run build

# 6. Establecer permisos
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Variables de Entorno Importantes

```env
# Aplicación
APP_NAME=CTH
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=cth

# API Móvil (futuro)
MOBILE_API_ENCRYPTION_KEY=base64:generated_key
```
MOBILE_JWT_SECRET=generated_secret
MOBILE_API_RATE_LIMIT=60

# Configuración de equipos
DEFAULT_EVENT_EXPIRATION_DAYS=7

### Configuración de Generación de PDF (Browsershot)

La aplicación utiliza `spatie/browsershot` (Puppeteer) para generar reportes en PDF. Esto requiere una configuración específica en el servidor.

#### Requisitos Previos
- **Node.js**: Versión 18+ (Recomendado v20 LTS)
- **NPM**: Compatible con la versión de Node
- **Librerías del sistema**: Dependencias de Chrome/Chromium

#### Instalación de Dependencias

1. **Instalar librerías del sistema (Debian/Ubuntu)**:
   ```bash
   sudo apt-get install -y gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget libgbm-dev
   ```

2. **Inicializar Puppeteer en el proyecto**:
   Hemos creado un script de utilidad para facilitar esto:
   ```bash
   php initialize_puppeteer.php
   ```

#### Configuración de Rutas de Node.js

En entornos de producción (especialmente con NVM o Cpanel), el usuario web puede no tener acceso al mismo PATH que el usuario de consola.

**Solución**: Configurar rutas explícitas en `app/Exports/EventsPdfExport.php`:

```php
// Ejemplo de configuración
$nodePath = '/home/usuario/.nvm/versions/node/v20.x.x/bin/node';
$npmPath = '/home/usuario/.nvm/versions/node/v20.x.x/bin/npm';

return Browsershot::html($html)
    ->setNodeBinary($nodePath)
    ->setNpmBinary($npmPath)
    ->setIncludePath('$PATH:' . dirname($nodePath))
    // ...
```

#### Troubleshooting Común

**Error**: `npm does not support Node.js v10.x.x`
- **Causa**: El servidor web está usando una versión antigua de Node del sistema (`/usr/bin/node`).
- **Solución**: Definir `setNodeBinary()` apuntando a la versión actualizada.

**Error**: `Could not open input file: initialize_puppeteer.php`
- **Causa**: El script no está en la raíz o permisos incorrectos.
- **Solución**: Verificar existencia del archivo y ejecutar `php initialize_puppeteer.php`.

**Error**: `Class Spatie\Browsershot\Browsershot not found`
- **Causa**: Paquete no instalado.
- **Solución**: Ejecutar `composer require spatie/browsershot`.

---

## 🚀 Funcionalidades Principales

### 1. Sistema de Fichaje Inteligente (SmartClockIn)

El componente central del sistema que gestiona automáticamente las acciones de fichaje.

#### Flujo de Trabajo

```
Usuario Inicia → 🟢 TRABAJANDO → [Pausar] → 🟠 EN PAUSA → [Continuar] → 🟢 TRABAJANDO → [Finalizar] → ⚪ FICHADO
```

#### Estados del Sistema

| Estado | Descripción | Acción Disponible |
|--------|-------------|-------------------|
| **Fichado** | Sin jornada activa | Iniciar Jornada |
| **Trabajando** | Jornada laboral activa | Pausar / Finalizar |
| **En Pausa** | Jornada pausada temporalmente | Continuar Trabajo |

#### Servicio SmartClockInService

**Ubicación**: `app/Services/SmartClockInService.php`

**Métodos principales**:

```php
// Determinar acción automática
public function getClockAction(User $user): array

// Iniciar jornada laboral
public function startWorkday(User $user, int $workdayEventTypeId): Event

// Pausar jornada
public function pauseWorkday(User $user, int $pauseEventTypeId): Event

// Reanudar trabajo
public function resumeWorkday(User $user, int $pauseEventId): Event

// Finalizar jornada
public function endWorkday(User $user, int $openEventId): Event
```

**Ejemplo de uso**:

```php
$service = new SmartClockInService();
$action = $service->getClockAction($user);

switch ($action['action']) {
    case 'start_workday':
        $event = $service->startWorkday($user, $workdayTypeId);
        break;
    case 'pause_workday':
        $event = $service->pauseWorkday($user, $pauseTypeId);
        break;
    case 'resume_workday':
        $event = $service->resumeWorkday($user, $pauseEventId);
        break;
    case 'end_workday':
        $event = $service->endWorkday($user, $openEventId);
        break;
}
```

### 2. Sistema de Pausas

Permite a los usuarios interrumpir temporalmente su jornada laboral.

#### Características

- **Tipo de evento específico**: "Pausa" (color naranja)
- **No cuenta como tiempo de trabajo**: `is_workday_type = false`
- **Múltiples pausas**: Permitidas en una misma jornada
- **Sin límite de duración**: Flexible según necesidades

#### Casos de Uso

1. **Cita médica**: Pausa durante la cita, continúa después
2. **Gestiones personales**: Trámites bancarios, oficiales, etc.
3. **Cambio de ubicación**: Pausa al salir, continúa en nueva ubicación

#### Implementación

**Migración**: `2025_11_06_174952_add_pause_event_type_to_teams.php`

```php
EventType::create([
    'team_id' => $team->id,
    'name' => 'Pausa',
    'color' => '#FFA500',
    'is_break_type' => true,
    'is_workday_type' => false,
    'max_duration_minutes' => null,
]);
```

### 3. Sistema NFC con Auto-Configuración

Permite configurar automáticamente la aplicación móvil mediante etiquetas NFC.

#### Estructura del Payload NFC

```json
{
    "server_url": "https://tu-cth-server.com",
    "api_endpoint": "https://tu-cth-server.com/api/v1",
    "nfc_tag_id": "CTH-673C8A7F-A1B2C3D4-1234567890ABCDEF",
    "work_center_id": 1,
    "work_center_code": "WC001",
    "team_id": 1,
    "generated_at": "2025-11-07T01:45:31.000Z",
    "version": "1.0"
}
```

#### Flujo de Auto-Configuración

1. **Administrador**: Habilita NFC en centro de trabajo
2. **Sistema**: Genera payload completo con URL del servidor
3. **Administrador**: Programa etiqueta NFC física con el payload
4. **Empleado**: Lee etiqueta NFC con app Flutter
5. **App**: Se configura automáticamente y verifica conexión
6. **Empleado**: Puede fichar inmediatamente

#### Métodos del Modelo WorkCenter

```php
// Generar payload NFC completo
public function generateNFCPayload(string $nfcId): string

// Habilitar NFC para el centro
public function enableNFC(?string $description = null): string

// Obtener datos del payload
public function getNFCPayloadData(): ?array
```

### 4. Sistema de Anuncios de Equipo

Permite a los administradores publicar anuncios para todo el equipo.

#### Características

- **Visibilidad controlada**: Por fechas de inicio/fin
- **Contenido rico**: Soporte HTML con editor WYSIWYG
- **Desplegable**: Interfaz colapsable en la página de inicio
- **Responsive**: Adaptado para móviles y desktop

#### Modelo TeamAnnouncement

**Campos principales**:
- `team_id`: Equipo al que pertenece
- `title`: Título del anuncio
- `content`: Contenido HTML
- `start_date`: Fecha de inicio de visibilidad
- `end_date`: Fecha de fin de visibilidad
- `created_by`: Usuario creador

#### Componente Livewire

**Ubicación**: `app/Http/Livewire/TeamAnnouncements.php`

```php
class TeamAnnouncements extends Component
{
    public function render()
    {
        $announcements = TeamAnnouncement::where('team_id', auth()->user()->currentTeam->id)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.team-announcements', [
            'announcements' => $announcements
        ]);
    }
}
```

### 5. Sistema de Horas Extra

Clasificación automática de eventos como horas extra o tiempo regular.

#### Lógica de Clasificación

```php
// Solo eventos de "Jornada laboral principal" NO son horas extra
$isExtraHours = !($eventType && $eventType->is_workday_type);
```

#### Tipos de Evento

| Tipo | is_workday_type | is_extra_hours |
|------|-----------------|----------------|
| Jornada laboral | true | false |
| Pausa | false | true |
| Vacaciones | false | true |
| Baja médica | false | true |
| Formación | false | true |

#### Migración de Datos Existentes

**Comando**: `php artisan events:update-extra-hours`

```bash
# Ver cambios sin aplicar
php artisan events:update-extra-hours --dry-run

# Aplicar actualización
php artisan events:update-extra-hours
```

---

## 🔌 API y Endpoints

### API Móvil v1

**Base URL**: `/api/v1/mobile`

#### 1. Fichaje (Clock Action)

**Endpoint**: `POST /api/v1/mobile/clock`

**Request**:
```json
{
  "work_center_code": "CTH001",
  "user_secret_code": "1234",
  "location": {
    "latitude": 40.4168,
    "longitude": -3.7038
  }
}
```

**Response Exitosa**:
```json
{
  "success": true,
  "action_taken": "clock_in",
  "message": "Successfully clocked in",
  "user": {
    "id": 1,
    "name": "Juan",
    "family_name1": "Pérez",
    "current_status": "working",
    "work_center": {
      "id": 1,
      "name": "Centro Principal",
      "code": "CTH001"
    }
  },
  "work_schedule": {
    "monday": {
      "slots": [
        {"start": "08:00", "end": "12:00"},
        {"start": "13:00", "end": "17:00"}
      ]
    }
  },
  "today_records": [
    {
      "id": 123,
      "type": "Jornada laboral",
      "start": "2024-11-06T08:00:00Z",
      "end": null,
      "is_closed": false
    }
  ],
  "server_time": "2024-11-06T08:30:00Z"
}
```

**Acciones Posibles**:
- `clock_in`: Iniciar jornada
- `break_start`: Iniciar pausa
- `break_end`: Finalizar pausa
- `clock_out`: Finalizar jornada

#### 2. Verificación NFC

**Endpoint**: `POST /api/v1/config/verify-nfc`

**Request**:
```json
{
  "nfc_data": "{\"server_url\":\"...\",\"nfc_tag_id\":\"...\",...}"
}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "work_center": {
      "id": 1,
      "name": "Oficina Principal",
      "code": "WC001",
      "team_id": 1
    },
    "verification": {
      "verified_at": "2025-11-07T01:45:31.000Z",
      "status": "verified"
    },
    "auto_configuration": {
      "server_configured": true,
      "server_url": "https://tu-cth-server.com",
      "api_endpoint": "https://tu-cth-server.com/api/v1"
    }
  },
  "message": "NFC verified and server auto-configuration data provided"
}
```

### Controlador API

**Ubicación**: `app/Http/Controllers/Api/MobileClockController.php`

```php
public function clock(Request $request)
{
    $validated = $request->validate([
        'work_center_code' => 'required|string',
        'user_secret_code' => 'required|string',
        'location' => 'nullable|array',
        'location.latitude' => 'required_with:location|numeric',
        'location.longitude' => 'required_with:location|numeric',
    ]);

    // Buscar centro de trabajo
    $workCenter = WorkCenter::where('code', $validated['work_center_code'])->first();
    
    // Buscar usuario
    $user = User::where('secret_code', $validated['user_secret_code'])
        ->where('current_team_id', $workCenter->team_id)
        ->first();

    // Ejecutar acción de fichaje
    $service = new SmartClockInService();
    $action = $service->getClockAction($user);
    
    // Procesar acción y retornar respuesta
    // ...
}
```

---

## 📱 Aplicación Móvil Flutter

### Arquitectura Clean Architecture

```
lib/
├── core/                    # Núcleo de la aplicación
│   ├── constants/          # Constantes y configuración
│   ├── errors/             # Manejo de errores
│   ├── network/            # Cliente HTTP
│   └── utils/              # Utilidades
├── data/                   # Capa de datos
│   ├── datasources/        # Fuentes de datos (API, local)
│   ├── models/             # Modelos de datos
│   └── repositories/       # Implementación de repositorios
├── domain/                 # Lógica de negocio
│   ├── entities/           # Entidades del dominio
│   ├── repositories/       # Interfaces de repositorios
│   └── usecases/           # Casos de uso
├── presentation/           # Capa de presentación
│   ├── providers/          # State management
│   ├── screens/            # Pantallas
│   ├── widgets/            # Widgets reutilizables
│   └── theme/              # Tema de la app
└── services/               # Servicios (location, QR, etc.)
```

### Dependencias Principales

```yaml
dependencies:
  flutter:
    sdk: flutter
  
  # Networking
  dio: ^5.3.2
  connectivity_plus: ^5.0.2
  
  # State Management
  provider: ^6.1.1
  
  # Storage
  shared_preferences: ^2.2.2
  hive: ^2.2.3
  hive_flutter: ^1.1.0
  flutter_secure_storage: ^9.0.0
  
  # QR/NFC
  qr_code_scanner: ^3.0.1
  nfc_manager: ^3.3.0
  
  # Location
  geolocator: ^10.1.0
  permission_handler: ^11.1.0
  
  # UI/UX
  intl: ^0.18.1
```

### Pantallas Principales

#### 1. Setup Screen (Configuración Inicial)

Permite configurar la conexión con el servidor CTH.

**Campos**:
- URL del servidor (opcional con NFC)
- Código de centro de trabajo
- Código secreto personal
- Toggle: Recordar credenciales

**Funcionalidad NFC**:
- Escanear etiqueta NFC para auto-configuración
- Extrae URL del servidor y código de centro automáticamente

#### 2. Home Screen (Pantalla Principal)

Pantalla principal de fichaje.

**Elementos**:
- Header con nombre de usuario y centro de trabajo
- Card de estado actual (Trabajando/En pausa/Fichado)
- Botón principal de acción
- Horario del día
- Últimos registros del día
- FAB para escaneo QR rápido

#### 3. Clock Screen (Pantalla de Fichaje)

Interfaz de fichaje con información detallada.

**Información mostrada**:
- Estado actual del trabajador
- Horas trabajadas hoy
- Tramo horario actual
- Hora de entrada
- Botones de acción contextuales

### Servicio de API

```dart
class ClockApiService {
  final Dio _dio;
  static const String baseUrl = 'https://your-domain.com/api/v1';
  
  Future<ApiResponse> performClockAction({
    required String workCenterCode,
    required String userSecretCode,
    Position? location,
  }) async {
    try {
      final response = await _dio.post(
        '$baseUrl/mobile/clock',
        data: {
          'work_center_code': workCenterCode,
          'user_secret_code': userSecretCode,
          if (location != null) 'location': {
            'latitude': location.latitude,
            'longitude': location.longitude,
          },
        },
      );
      
      return ApiResponse.fromJson(response.data);
    } on DioException catch (e) {
      throw ClockException('Error en fichaje: ${e.message}');
    }
  }
}
```

### State Management con Provider

```dart
class ClockProvider extends ChangeNotifier {
  ClockStatus _status = ClockStatus.loggedOut;
  User? _user;
  List<TimeRecord> _todayRecords = [];
  bool _isLoading = false;
  
  Future<void> performClockAction() async {
    setLoading(true);
    try {
      final location = await _locationService.getCurrentLocation();
      final response = await _apiService.performClockAction(
        workCenterCode: _credentials.workCenterCode,
        userSecretCode: _credentials.userSecretCode,
        location: location,
      );
      
      _updateStateFromResponse(response);
      await _saveToLocalStorage(response);
      
      notifyListeners();
    } catch (e) {
      _handleError(e);
    } finally {
      setLoading(false);
    }
  }
}
```

### Build y Deployment

```bash
# Debug build
flutter build apk --debug

# Release build con ofuscación
flutter build apk --release --obfuscate --split-debug-info=debug-info/

# App Bundle para Play Store
flutter build appbundle --release --obfuscate --split-debug-info=debug-info/
```

---

## 🛠️ Comandos de Consola

### Gestión de Eventos

#### 1. events:autoclose

Cierra automáticamente eventos no confirmados que han pasado su fecha de expiración.

```bash
php artisan events:autoclose
```

**Funcionalidad**:
- Revisa equipos con `event_expiration_days` configurado
- Cierra eventos abiertos que exceden el tiempo límite
- Registra en logs cada evento cerrado
- Actualiza `is_open = false` y `is_closed_automatically = true`

**Uso en cron**:
```bash
# Ejecutar diariamente a las 2:00 AM
0 2 * * * cd /path/to/cth && php artisan events:autoclose
```

#### 2. events:fix-data

Analiza y corrige eventos con problemas de datos.

```bash
# Ver problemas sin corregir
php artisan events:fix-data --dry-run

# Corregir problemas
php artisan events:fix-data

# Analizar usuario específico
php artisan events:fix-data --user=123 --dry-run

# Analizar rango de fechas
php artisan events:fix-data --from=2023-01-01 --to=2023-12-31
```

**Problemas que corrige**:
- Eventos sin fecha de fin (`end = null`)
- Eventos sin tipo (`event_type_id = null`)
- Eventos con `start > end`
- Eventos con duraciones anómalas

#### 3. events:update-extra-hours

Actualiza eventos existentes con nueva lógica de horas extra.

```bash
# Ver qué cambiaría
php artisan events:update-extra-hours --dry-run

# Aplicar cambios
php artisan events:update-extra-hours
```

**Funcionalidad**:
- Aplica nueva lógica: solo eventos de "jornada laboral principal" NO son horas extra
- Actualiza campo `is_extra_hours` en todos los eventos
- Maneja eventos sin tipo de evento
- Reporte detallado de cambios

#### 4. events:verify-and-fix

Comando integral de verificación y corrección de inconsistencias.

```bash
# Verificación completa (solo mostrar)
php artisan events:verify-and-fix --dry-run

# Corregir todo
php artisan events:verify-and-fix

# Corregir solo descripciones
php artisan events:verify-and-fix --fix-descriptions

# Corregir solo horas extra
php artisan events:verify-and-fix --fix-extra-hours

# Corregir solo tipos de jornada laboral
php artisan events:verify-and-fix --fix-workday-types
```

**Verificaciones incluidas**:
- Tipos de evento "Jornada laboral" marcados correctamente
- Eventos con descripciones apropiadas
- Lógica de horas extra consistente
- Eventos huérfanos (sin tipo)
- Integridad general de datos

### Mantenimiento de Base de Datos

```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar migraciones paso a paso
php artisan migrate --step

# Revertir última migración
php artisan migrate:rollback

# Ver estado de migraciones
php artisan migrate:status
```

### Cache y Optimización

```bash
# Limpiar caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Flujos de Trabajo Recomendados

#### Mantenimiento Diario (Cron Jobs)

```bash
# 2:00 AM - Cerrar eventos automáticamente
0 2 * * * cd /path/to/cth && php artisan events:autoclose

# 3:00 AM - Verificar y corregir datos (semanal)
0 3 * * 0 cd /path/to/cth && php artisan events:verify-and-fix
```

#### Después de Actualizaciones

```bash
# 1. Ejecutar migraciones
php artisan migrate

# 2. Verificar estado
php artisan events:verify-and-fix --dry-run

# 3. Corregir si es necesario
php artisan events:verify-and-fix

# 4. Optimizar caches
php artisan config:cache
php artisan route:cache
```

---

## 🔐 Seguridad

### Arquitectura de Seguridad Actual

#### Fase 1: Implementación Básica (Actual)

- ✅ **HTTPS obligatorio** (TLS 1.2+)
- ✅ **Validación de certificados SSL**
- ✅ **Timeouts y rate limiting**
- ✅ **Validación de entrada y sanitización**
- ✅ **Autenticación Laravel Jetstream**
- ✅ **Protección CSRF**

#### Configuración de Seguridad

```env
# .env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Rate limiting
MOBILE_API_RATE_LIMIT=60  # requests per minute
```

### Seguridad en API Móvil

#### Validación de Requests

```php
$validated = $request->validate([
    'work_center_code' => 'required|string|max:50',
    'user_secret_code' => 'required|string|max:50',
    'location' => 'nullable|array',
    'location.latitude' => 'required_with:location|numeric|between:-90,90',
    'location.longitude' => 'required_with:location|numeric|between:-180,180',
]);
```

#### Rate Limiting

```php
// routes/api.php
Route::middleware(['throttle:mobile'])->group(function () {
    Route::post('/mobile/clock', [MobileClockController::class, 'clock']);
});

// app/Providers/RouteServiceProvider.php
RateLimiter::for('mobile', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});
```

### Seguridad Futura (Roadmap)

#### Fase 2: Encriptación Simétrica

- [ ] AES-256-GCM para datos sensibles
- [ ] Timestamp validation (anti-replay)
- [ ] Secure storage en app móvil
- [ ] Key rotation mensual

#### Fase 3: Autenticación Avanzada

- [ ] JWT tokens con scopes limitados
- [ ] Refresh token mechanism
- [ ] Device fingerprinting
- [ ] Geolocation validation

#### Fase 4: Seguridad Empresarial

- [ ] Diffie-Hellman key exchange
- [ ] Certificate pinning
- [ ] Audit logging completo
- [ ] Intrusion detection

### Auditoría de Seguridad

#### Anuncios de Equipo

**Vulnerabilidades corregidas**:
- ✅ XSS mediante validación y sanitización de HTML
- ✅ Inyección SQL mediante Eloquent ORM
- ✅ Control de acceso basado en equipos
- ✅ Validación de fechas y rangos

**Implementación**:

```php
// Validación de entrada
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'content' => 'required|string|max:10000',
    'start_date' => 'nullable|date',
    'end_date' => 'nullable|date|after_or_equal:start_date',
]);

// Sanitización de HTML
$announcement->content = strip_tags(
    $validated['content'],
    '<p><br><strong><em><ul><ol><li><a><h1><h2><h3>'
);

// Control de acceso
if ($announcement->team_id !== auth()->user()->currentTeam->id) {
    abort(403);
}
```

---

## 🧪 Testing y QA

### Testing Backend (Laravel)

#### Unit Tests

```php
// tests/Unit/SmartClockInServiceTest.php
class SmartClockInServiceTest extends TestCase
{
    public function test_start_workday_creates_event()
    {
        $user = User::factory()->create();
        $service = new SmartClockInService();
        
        $event = $service->startWorkday($user, 1);
        
        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals($user->id, $event->user_id);
        $this->assertTrue($event->is_open);
    }
    
    public function test_pause_workday_closes_previous_event()
    {
        $user = User::factory()->create();
        $service = new SmartClockInService();
        
        $workdayEvent = $service->startWorkday($user, 1);
        $pauseEvent = $service->pauseWorkday($user, 2);
        
        $this->assertFalse($workdayEvent->fresh()->is_open);
        $this->assertTrue($pauseEvent->is_open);
    }
}
```

#### Feature Tests

```php
// tests/Feature/MobileApiTest.php
class MobileApiTest extends TestCase
{
    public function test_clock_in_with_valid_credentials()
    {
        $workCenter = WorkCenter::factory()->create(['code' => 'TEST001']);
        $user = User::factory()->create(['secret_code' => '1234']);
        
        $response = $this->postJson('/api/v1/mobile/clock', [
            'work_center_code' => 'TEST001',
            'user_secret_code' => '1234',
        ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'action_taken' => 'clock_in',
            ]);
    }
}
```

### Testing Frontend (Flutter)

#### Unit Tests

```dart
// test/services/clock_api_service_test.dart
void main() {
  group('ClockApiService', () {
    test('performClockAction returns success response', () async {
      final mockDio = MockDio();
      final service = ClockApiService(dio: mockDio);
      
      when(mockDio.post(any, data: anyNamed('data')))
          .thenAnswer((_) async => Response(
                data: {'success': true, 'action_taken': 'clock_in'},
                statusCode: 200,
              ));
      
      final result = await service.performClockAction(
        workCenterCode: 'TEST001',
        userSecretCode: '1234',
      );
      
      expect(result.success, true);
      expect(result.actionTaken, 'clock_in');
    });
  });
}
```

#### Widget Tests

```dart
// test/widgets/clock_button_test.dart
void main() {
  testWidgets('ClockButton shows correct text for clock_in action', 
    (WidgetTester tester) async {
    await tester.pumpWidget(
      MaterialApp(
        home: ClockButton(action: ClockAction.clockIn),
      ),
    );
    
    expect(find.text('Iniciar Jornada'), findsOneWidget);
  });
}
```

### Estrategia de Testing

#### Cobertura Mínima

- **Backend**: 70% de cobertura de código
- **Frontend**: 60% de cobertura de código
- **Critical paths**: 100% de cobertura

#### Tests Automatizados

```bash
# Backend (Laravel)
php artisan test
php artisan test --coverage

# Frontend (Flutter)
flutter test
flutter test --coverage
```

---

## 🔧 Mantenimiento y Troubleshooting

### Problemas Comunes

#### 1. "No se encontraron eventos para procesar"

**Causa**: Filtros de fecha demasiado restrictivos

**Solución**:
```bash
php artisan events:fix-data --from=2023-01-01 --to=2025-12-31 --dry-run
```

#### 2. "Muchos eventos sin tipo"

**Causa**: Eventos antiguos sin tipo asignado

**Solución**:
```bash
# Corregir tipos de evento primero
php artisan events:verify-and-fix --fix-workday-types
```

#### 3. "Problemas de rendimiento con grandes volúmenes"

**Causa**: Procesamiento de demasiados eventos a la vez

**Solución**:
```bash
# Procesar por rangos de fecha
php artisan events:fix-data --from=2023-01-01 --to=2023-06-30
php artisan events:fix-data --from=2023-07-01 --to=2023-12-31
```

#### 4. "App móvil no se conecta al servidor"

**Causas posibles**:
- URL del servidor incorrecta
- Certificado SSL inválido
- Firewall bloqueando conexión
- Credenciales incorrectas

**Solución**:
```dart
// Verificar configuración
print('Server URL: ${ConfigService.serverUrl}');
print('API Endpoint: ${ConfigService.apiEndpoint}');

// Test de conectividad
final response = await dio.get('${serverUrl}/api/health');
```

#### 5. "Eventos no se cierran automáticamente"

**Causa**: Cron job no configurado

**Solución**:
```bash
# Verificar crontab
crontab -l

# Añadir si no existe
0 2 * * * cd /path/to/cth && php artisan events:autoclose
```

### Logging y Monitoreo

#### Archivos de Log

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Filtrar por nivel
grep "ERROR" storage/logs/laravel.log

# Ver logs de comandos
grep "AutoCloseEvents" storage/logs/laravel.log
```

#### Ejemplos de Logs

```
[2025-11-06 16:30:00] local.INFO: Starting AutoCloseEvents command...
[2025-11-06 16:30:01] local.INFO: Closing event 12345 for user 67 in team 1.
[2025-11-06 16:30:05] local.INFO: AutoCloseEvents completed. Closed 15 events.

[2025-11-06 16:35:00] local.INFO: Migration: Updated 185 events with new extra hours logic
[2025-11-06 16:35:00] local.INFO: Verification: Found 99 events without event type
```

### Monitoreo de Seguridad

#### Métricas a Vigilar

- Intentos de descifrado fallidos
- Requests con timestamps inválidos
- Geolocalizaciones sospechosas
- Múltiples intentos con códigos incorrectos
- Patrones de uso anómalos

#### Implementación

```php
class SecurityMonitoringService
{
    public function logSecurityEvent(string $event, array $context): void
    {
        Log::channel('security')->info("Security Event: $event", [
            'timestamp' => now(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $context
        ]);
    }
    
    public function detectSuspiciousActivity(string $userId): bool
    {
        // Check for multiple failed attempts
        // Unusual geolocation patterns
        // Timestamp anomalies
        return $this->analyzeUserBehavior($userId);
    }
}
```

### Backup y Recuperación

#### Backup de Base de Datos

```bash
# Backup completo
mysqldump -u usuario -p cth > backup_$(date +%Y%m%d).sql

# Backup con compresión
mysqldump -u usuario -p cth | gzip > backup_$(date +%Y%m%d).sql.gz

# Restaurar backup
mysql -u usuario -p cth < backup_20251106.sql
```

#### Backup de Archivos

```bash
# Backup de storage
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Backup completo de la aplicación
tar -czf cth_backup_$(date +%Y%m%d).tar.gz \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage/logs' \
  /path/to/cth
```

### Optimización de Rendimiento

#### Base de Datos

```sql
-- Índices importantes
CREATE INDEX idx_events_user_date ON events(user_id, start);
CREATE INDEX idx_events_team_date ON events(team_id, start);
CREATE INDEX idx_events_open ON events(is_open);

-- Analizar tablas
ANALYZE TABLE events;
ANALYZE TABLE users;
ANALYZE TABLE work_centers;
```

#### Laravel

```bash
# Optimizar autoloader
composer dump-autoload --optimize

# Cache de configuración
php artisan config:cache

# Cache de rutas
php artisan route:cache

# Cache de vistas
php artisan view:cache

# Optimización completa
php artisan optimize
```

#### Servidor Web

```nginx
# nginx.conf - Compresión gzip
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript 
           application/x-javascript application/xml+rss 
           application/json application/javascript;

# Cache de assets estáticos
location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

---

## 📚 Recursos Adicionales

### Documentación de Referencia

- **Laravel**: https://laravel.com/docs
- **Livewire**: https://livewire.laravel.com/docs
- **Flutter**: https://flutter.dev/docs
- **Tailwind CSS**: https://tailwindcss.com/docs

### Convenciones de Código

#### PHP/Laravel

- **PSR-12**: Estándar de codificación
- **Nombres de clases**: PascalCase
- **Nombres de métodos**: camelCase
- **Nombres de variables**: snake_case (BD), camelCase (código)

#### Dart/Flutter

- **Effective Dart**: Guía de estilo oficial
- **Nombres de clases**: PascalCase
- **Nombres de métodos**: camelCase
- **Nombres de archivos**: snake_case

### Control de Versiones

#### Estrategia de Branching

```
main (producción)
  ├── develop (desarrollo)
  │   ├── feature/nueva-funcionalidad
  │   ├── fix/correccion-bug
  │   └── hotfix/arreglo-urgente
```

#### Commits Semánticos

```
feat: Nueva funcionalidad
fix: Corrección de bug
docs: Cambios en documentación
style: Cambios de formato (no afectan código)
refactor: Refactorización de código
test: Añadir o modificar tests
chore: Tareas de mantenimiento
```

---

## 📞 Soporte y Contacto

Para problemas, sugerencias o consultas:

1. **Revisar logs**: `storage/logs/laravel.log`
2. **Ejecutar diagnóstico**: `php artisan events:verify-and-fix --dry-run`
3. **Consultar documentación**: Este manual y archivos en `/docs`
4. **Contactar equipo de desarrollo**: [Tu información de contacto]

---

**Versión del Manual**: 1.0  
**Última actualización**: Noviembre 2025  
**Mantenido por**: Equipo de Desarrollo CTH

---

✅ **Este manual es un documento vivo y debe actualizarse con cada cambio significativo en el sistema.**
