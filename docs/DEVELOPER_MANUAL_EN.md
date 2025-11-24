# 📚 Developer Manual - CTH (Time and Schedule Control)

**Version**: 2025.11  
**Last updated**: November 2025  
**Status**: ✅ Production

---

## 📋 Table of Contents

1. [Introduction](#introduction)
2. [System Architecture](#system-architecture)
3. [Configuration and Deployment](#configuration-and-deployment)
4. [Main Features](#main-features)
5. [API and Endpoints](#api-and-endpoints)
6. [Flutter Mobile Application](#flutter-mobile-application)
7. [Console Commands](#console-commands)
8. [Security](#security)
9. [Testing and QA](#testing-and-qa)
10. [Maintenance and Troubleshooting](#maintenance-and-troubleshooting)

---

## 🎯 Introduction

CTH (Time and Schedule Control) is a comprehensive time tracking and schedule management system developed with Laravel (backend) and Flutter (mobile application). The system allows workers to register their clock-in, breaks, and clock-out, while providing administrators with complete management and reporting tools.

### System Components

- **Backend**: Laravel 9+ with Livewire
- **Web Frontend**: Blade templates + Tailwind CSS + Alpine.js
- **Mobile Application**: Flutter 3.16+ (Android)
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Jetstream

---

## 🏗️ System Architecture

### Technology Stack

#### Backend (Laravel)
```
Laravel 9+
├── Livewire (reactive components)
├── Jetstream (authentication and teams)
├── Tailwind CSS (styling)
├── Alpine.js (interactivity)
└── MySQL/MariaDB (database)
```

#### Mobile Frontend (Flutter)
```
Flutter 3.16+
├── Provider/Riverpod (state management)
├── Dio (HTTP client)
├── Hive/SQLite (local storage)
├── QR Code Scanner (QR/NFC reading)
└── Geolocator (geolocation)
```

### Directory Structure (Laravel)

```
cth/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Livewire/          # Livewire components
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic
│   │   └── SmartClockInService.php
│   └── Console/
│       └── Commands/          # Artisan commands
├── database/
│   ├── migrations/            # DB migrations
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── livewire/         # Livewire views
│   │   └── layouts/
│   └── lang/                  # Translations
├── routes/
│   ├── web.php
│   └── api.php               # Mobile API routes
├── public/
└── docs/                     # Documentation
```

### Main Models

#### User
- System worker
- Belongs to a team
- Has secret code for mobile clock-in
- Associated with schedules and events

#### Team
- Organization or company
- Contains users and work centers
- Event type configuration
- Announcement configuration

#### WorkCenter
- Physical work location
- NFC/QR support
- Associated with a team

#### Event
- Clock-in record
- Types: Workday, Break, Vacation, etc.
- Fields: start, end, event_type_id, user_id
- Flags: is_open, is_extra_hours

#### EventType
- Defines event categories
- Team-specific configuration
- Flags: is_workday_type, is_break_type
- Duration limits

---

## ⚙️ Configuration and Deployment

### System Requirements

- **PHP**: 8.1+
- **Composer**: 2.x
- **Node.js**: 16+ (for asset compilation)
- **MySQL/MariaDB**: 5.7+ / 10.3+
- **Web Server**: Apache/Nginx

### Initial Installation

```bash
# 1. Clone repository
git clone https://github.com/your-org/cth.git
cd cth

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cth
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 6. Run migrations
php artisan migrate

# 7. Compile assets
npm run dev  # Development
npm run build  # Production

# 8. Start server
php artisan serve
```

### Production Deployment

```bash
# 1. Update code
git pull origin main

# 2. Update dependencies
composer install --optimize-autoloader --no-dev

# 3. Run migrations
php artisan migrate --force

# 4. Clear and optimize caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Compile assets for production
npm run build

# 6. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Important Environment Variables

```env
# Application
APP_NAME=CTH
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=cth

# Mobile API (future)
MOBILE_API_ENCRYPTION_KEY=base64:generated_key
MOBILE_JWT_SECRET=generated_secret
MOBILE_API_RATE_LIMIT=60

# Team configuration
DEFAULT_EVENT_EXPIRATION_DAYS=7

### PDF Generation Configuration (Browsershot)

The application uses `spatie/browsershot` (Puppeteer) to generate PDF reports. This requires specific server configuration.

#### Prerequisites
- **Node.js**: Version 18+ (Recommended v20 LTS)
- **NPM**: Compatible with Node version
- **System Libraries**: Chrome/Chromium dependencies

#### Dependency Installation

1. **Install system libraries (Debian/Ubuntu)**:
   ```bash
   sudo apt-get install -y gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget libgbm-dev
   ```

2. **Initialize Puppeteer in the project**:
   We have created a utility script to facilitate this:
   ```bash
   php initialize_puppeteer.php
   ```

#### Node.js Path Configuration

In production environments (especially with NVM or Cpanel), the web user might not have access to the same PATH as the console user.

**Solution**: Configure explicit paths in `app/Exports/EventsPdfExport.php`:

```php
// Configuration example
$nodePath = '/home/user/.nvm/versions/node/v20.x.x/bin/node';
$npmPath = '/home/user/.nvm/versions/node/v20.x.x/bin/npm';

return Browsershot::html($html)
    ->setNodeBinary($nodePath)
    ->setNpmBinary($npmPath)
    ->setIncludePath('$PATH:' . dirname($nodePath))
    // ...
```

#### Common Troubleshooting

**Error**: `npm does not support Node.js v10.x.x`
- **Cause**: Web server is using an old system Node version (`/usr/bin/node`).
- **Solution**: Define `setNodeBinary()` pointing to the updated version.

**Error**: `Could not open input file: initialize_puppeteer.php`
- **Cause**: Script is not in root or incorrect permissions.
- **Solution**: Verify file existence and run `php initialize_puppeteer.php`.

**Error**: `Class Spatie\Browsershot\Browsershot not found`
- **Cause**: Package not installed.
- **Solution**: Run `composer require spatie/browsershot`.
```

---

## 🚀 Main Features

### 1. Smart Clock-In System (SmartClockIn)

The central system component that automatically manages clock-in actions.

#### Workflow

```
User Starts → 🟢 WORKING → [Pause] → 🟠 ON BREAK → [Continue] → 🟢 WORKING → [Finish] → ⚪ CLOCKED OUT
```

#### System States

| State | Description | Available Action |
|-------|-------------|------------------|
| **Clocked Out** | No active workday | Start Workday |
| **Working** | Active workday | Pause / Finish |
| **On Break** | Temporarily paused workday | Continue Work |

#### SmartClockInService

**Location**: `app/Services/SmartClockInService.php`

**Main methods**:

```php
// Determine automatic action
public function getClockAction(User $user): array

// Start workday
public function startWorkday(User $user, int $workdayEventTypeId): Event

// Pause workday
public function pauseWorkday(User $user, int $pauseEventTypeId): Event

// Resume work
public function resumeWorkday(User $user, int $pauseEventId): Event

// End workday
public function endWorkday(User $user, int $openEventId): Event
```

**Usage example**:

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

### 2. Break System

Allows users to temporarily interrupt their workday.

#### Features

- **Specific event type**: "Break" (orange color)
- **Does not count as work time**: `is_workday_type = false`
- **Multiple breaks**: Allowed in a single workday
- **No duration limit**: Flexible according to needs

#### Use Cases

1. **Medical appointments**: Break during appointment, continue after
2. **Personal errands**: Banking, official procedures, etc.
3. **Location changes**: Break when leaving, continue at new location

#### Implementation

**Migration**: `2025_11_06_174952_add_pause_event_type_to_teams.php`

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

### 3. NFC Auto-Configuration System

Allows automatic mobile app configuration via NFC tags.

#### NFC Payload Structure

```json
{
    "server_url": "https://your-cth-server.com",
    "api_endpoint": "https://your-cth-server.com/api/v1",
    "nfc_tag_id": "CTH-673C8A7F-A1B2C3D4-1234567890ABCDEF",
    "work_center_id": 1,
    "work_center_code": "WC001",
    "team_id": 1,
    "generated_at": "2025-11-07T01:45:31.000Z",
    "version": "1.0"
}
```

#### Auto-Configuration Flow

1. **Administrator**: Enables NFC on work center
2. **System**: Generates complete payload with server URL
3. **Administrator**: Programs physical NFC tag with payload
4. **Employee**: Reads NFC tag with Flutter app
5. **App**: Automatically configures itself and verifies connection
6. **Employee**: Can clock in immediately

#### WorkCenter Model Methods

```php
// Generate complete NFC payload
public function generateNFCPayload(string $nfcId): string

// Enable NFC for center
public function enableNFC(?string $description = null): string

// Get payload data
public function getNFCPayloadData(): ?array
```

### 4. Team Announcements System

Allows administrators to publish announcements for the entire team.

#### Features

- **Controlled visibility**: By start/end dates
- **Rich content**: HTML support with WYSIWYG editor
- **Collapsible**: Collapsible interface on home page
- **Responsive**: Adapted for mobile and desktop

#### TeamAnnouncement Model

**Main fields**:
- `team_id`: Team it belongs to
- `title`: Announcement title
- `content`: HTML content
- `start_date`: Visibility start date
- `end_date`: Visibility end date
- `created_by`: Creator user

#### Livewire Component

**Location**: `app/Http/Livewire/TeamAnnouncements.php`

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

### 5. Overtime System

Automatic classification of events as overtime or regular time.

#### Classification Logic

```php
// Only "main workday" events are NOT overtime
$isExtraHours = !($eventType && $eventType->is_workday_type);
```

#### Event Types

| Type | is_workday_type | is_extra_hours |
|------|-----------------|----------------|
| Workday | true | false |
| Break | false | true |
| Vacation | false | true |
| Sick leave | false | true |
| Training | false | true |

#### Existing Data Migration

**Command**: `php artisan events:update-extra-hours`

```bash
# View changes without applying
php artisan events:update-extra-hours --dry-run

# Apply update
php artisan events:update-extra-hours
```

---

## 🔌 API and Endpoints

### Mobile API v1

**Base URL**: `/api/v1/mobile`

#### 1. Clock Action

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

**Successful Response**:
```json
{
  "success": true,
  "action_taken": "clock_in",
  "message": "Successfully clocked in",
  "user": {
    "id": 1,
    "name": "John",
    "family_name1": "Doe",
    "current_status": "working",
    "work_center": {
      "id": 1,
      "name": "Main Center",
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
      "type": "Workday",
      "start": "2024-11-06T08:00:00Z",
      "end": null,
      "is_closed": false
    }
  ],
  "server_time": "2024-11-06T08:30:00Z"
}
```

**Possible Actions**:
- `clock_in`: Start workday
- `break_start`: Start break
- `break_end`: End break
- `clock_out`: End workday

#### 2. NFC Verification

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
      "name": "Main Office",
      "code": "WC001",
      "team_id": 1
    },
    "verification": {
      "verified_at": "2025-11-07T01:45:31.000Z",
      "status": "verified"
    },
    "auto_configuration": {
      "server_configured": true,
      "server_url": "https://your-cth-server.com",
      "api_endpoint": "https://your-cth-server.com/api/v1"
    }
  },
  "message": "NFC verified and server auto-configuration data provided"
}
```

### API Controller

**Location**: `app/Http/Controllers/Api/MobileClockController.php`

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

    // Find work center
    $workCenter = WorkCenter::where('code', $validated['work_center_code'])->first();
    
    // Find user
    $user = User::where('secret_code', $validated['user_secret_code'])
        ->where('current_team_id', $workCenter->team_id)
        ->first();

    // Execute clock action
    $service = new SmartClockInService();
    $action = $service->getClockAction($user);
    
    // Process action and return response
    // ...
}
```

---

## 📱 Flutter Mobile Application

### Clean Architecture

```
lib/
├── core/                    # Application core
│   ├── constants/          # Constants and configuration
│   ├── errors/             # Error handling
│   ├── network/            # HTTP client
│   └── utils/              # Utilities
├── data/                   # Data layer
│   ├── datasources/        # Data sources (API, local)
│   ├── models/             # Data models
│   └── repositories/       # Repository implementations
├── domain/                 # Business logic
│   ├── entities/           # Domain entities
│   ├── repositories/       # Repository interfaces
│   └── usecases/           # Use cases
├── presentation/           # Presentation layer
│   ├── providers/          # State management
│   ├── screens/            # Screens
│   ├── widgets/            # Reusable widgets
│   └── theme/              # App theme
└── services/               # Services (location, QR, etc.)
```

### Main Dependencies

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

### Main Screens

#### 1. Setup Screen (Initial Configuration)

Allows configuring the connection with the CTH server.

**Fields**:
- Server URL (optional with NFC)
- Work center code
- Personal secret code
- Toggle: Remember credentials

**NFC Functionality**:
- Scan NFC tag for auto-configuration
- Automatically extracts server URL and center code

#### 2. Home Screen

Main clock-in screen.

**Elements**:
- Header with username and work center
- Current status card (Working/On break/Clocked out)
- Main action button
- Today's schedule
- Latest records of the day
- FAB for quick QR scan

#### 3. Clock Screen

Clock-in interface with detailed information.

**Information displayed**:
- Worker's current status
- Hours worked today
- Current time slot
- Clock-in time
- Contextual action buttons

### API Service

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
      throw ClockException('Clock-in error: ${e.message}');
    }
  }
}
```

### State Management with Provider

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

### Build and Deployment

```bash
# Debug build
flutter build apk --debug

# Release build with obfuscation
flutter build apk --release --obfuscate --split-debug-info=debug-info/

# App Bundle for Play Store
flutter build appbundle --release --obfuscate --split-debug-info=debug-info/
```

---

## 🛠️ Console Commands

### Event Management

#### 1. events:autoclose

Automatically closes unconfirmed events that have passed their expiration date.

```bash
php artisan events:autoclose
```

**Functionality**:
- Reviews teams with `event_expiration_days` configured
- Closes open events exceeding time limit
- Logs each closed event
- Updates `is_open = false` and `is_closed_automatically = true`

**Cron usage**:
```bash
# Run daily at 2:00 AM
0 2 * * * cd /path/to/cth && php artisan events:autoclose
```

#### 2. events:fix-data

Analyzes and corrects events with data problems.

```bash
# View problems without correcting
php artisan events:fix-data --dry-run

# Correct problems
php artisan events:fix-data

# Analyze specific user
php artisan events:fix-data --user=123 --dry-run

# Analyze date range
php artisan events:fix-data --from=2023-01-01 --to=2023-12-31
```

**Problems it fixes**:
- Events without end date (`end = null`)
- Events without type (`event_type_id = null`)
- Events with `start > end`
- Events with anomalous durations

#### 3. events:update-extra-hours

Updates existing events with new overtime logic.

```bash
# View what would change
php artisan events:update-extra-hours --dry-run

# Apply changes
php artisan events:update-extra-hours
```

**Functionality**:
- Applies new logic: only "main workday" events are NOT overtime
- Updates `is_extra_hours` field in all events
- Handles events without event type
- Detailed change report

#### 4. events:verify-and-fix

Comprehensive command for verification and correction of inconsistencies.

```bash
# Complete verification (show only)
php artisan events:verify-and-fix --dry-run

# Fix everything
php artisan events:verify-and-fix

# Fix only descriptions
php artisan events:verify-and-fix --fix-descriptions

# Fix only overtime
php artisan events:verify-and-fix --fix-extra-hours

# Fix only workday types
php artisan events:verify-and-fix --fix-workday-types
```

**Included verifications**:
- "Workday" event types correctly marked
- Events with appropriate descriptions
- Consistent overtime logic
- Orphan events (without type)
- General data integrity

### Database Maintenance

```bash
# Run migrations
php artisan migrate

# Run migrations step by step
php artisan migrate --step

# Rollback last migration
php artisan migrate:rollback

# View migration status
php artisan migrate:status
```

### Cache and Optimization

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Recommended Workflows

#### Daily Maintenance (Cron Jobs)

```bash
# 2:00 AM - Close events automatically
0 2 * * * cd /path/to/cth && php artisan events:autoclose

# 3:00 AM - Verify and fix data (weekly)
0 3 * * 0 cd /path/to/cth && php artisan events:verify-and-fix
```

#### After Updates

```bash
# 1. Run migrations
php artisan migrate

# 2. Verify status
php artisan events:verify-and-fix --dry-run

# 3. Fix if necessary
php artisan events:verify-and-fix

# 4. Optimize caches
php artisan config:cache
php artisan route:cache
```

---

## 🔐 Security

### Current Security Architecture

#### Phase 1: Basic Implementation (Current)

- ✅ **Mandatory HTTPS** (TLS 1.2+)
- ✅ **SSL certificate validation**
- ✅ **Timeouts and rate limiting**
- ✅ **Input validation and sanitization**
- ✅ **Laravel Jetstream authentication**
- ✅ **CSRF protection**

#### Security Configuration

```env
# .env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Rate limiting
MOBILE_API_RATE_LIMIT=60  # requests per minute
```

### Mobile API Security

#### Request Validation

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

### Future Security (Roadmap)

#### Phase 2: Symmetric Encryption

- [ ] AES-256-GCM for sensitive data
- [ ] Timestamp validation (anti-replay)
- [ ] Secure storage in mobile app
- [ ] Monthly key rotation

#### Phase 3: Advanced Authentication

- [ ] JWT tokens with limited scopes
- [ ] Refresh token mechanism
- [ ] Device fingerprinting
- [ ] Geolocation validation

#### Phase 4: Enterprise Security

- [ ] Diffie-Hellman key exchange
- [ ] Certificate pinning
- [ ] Complete audit logging
- [ ] Intrusion detection

### Security Audit

#### Team Announcements

**Fixed vulnerabilities**:
- ✅ XSS via HTML validation and sanitization
- ✅ SQL injection via Eloquent ORM
- ✅ Team-based access control
- ✅ Date and range validation

**Implementation**:

```php
// Input validation
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'content' => 'required|string|max:10000',
    'start_date' => 'nullable|date',
    'end_date' => 'nullable|date|after_or_equal:start_date',
]);

// HTML sanitization
$announcement->content = strip_tags(
    $validated['content'],
    '<p><br><strong><em><ul><ol><li><a><h1><h2><h3>'
);

// Access control
if ($announcement->team_id !== auth()->user()->currentTeam->id) {
    abort(403);
}
```

---

## 🧪 Testing and QA

### Backend Testing (Laravel)

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

### Frontend Testing (Flutter)

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
    
    expect(find.text('Start Workday'), findsOneWidget);
  });
}
```

### Testing Strategy

#### Minimum Coverage

- **Backend**: 70% code coverage
- **Frontend**: 60% code coverage
- **Critical paths**: 100% coverage

#### Automated Tests

```bash
# Backend (Laravel)
php artisan test
php artisan test --coverage

# Frontend (Flutter)
flutter test
flutter test --coverage
```

---

## 🔧 Maintenance and Troubleshooting

### Common Problems

#### 1. "No events found to process"

**Cause**: Date filters too restrictive

**Solution**:
```bash
php artisan events:fix-data --from=2023-01-01 --to=2025-12-31 --dry-run
```

#### 2. "Many events without type"

**Cause**: Old events without assigned type

**Solution**:
```bash
# Fix event types first
php artisan events:verify-and-fix --fix-workday-types
```

#### 3. "Performance issues with large volumes"

**Cause**: Processing too many events at once

**Solution**:
```bash
# Process by date ranges
php artisan events:fix-data --from=2023-01-01 --to=2023-06-30
php artisan events:fix-data --from=2023-07-01 --to=2023-12-31
```

#### 4. "Mobile app doesn't connect to server"

**Possible causes**:
- Incorrect server URL
- Invalid SSL certificate
- Firewall blocking connection
- Incorrect credentials

**Solution**:
```dart
// Verify configuration
print('Server URL: ${ConfigService.serverUrl}');
print('API Endpoint: ${ConfigService.apiEndpoint}');

// Connectivity test
final response = await dio.get('${serverUrl}/api/health');
```

#### 5. "Events don't close automatically"

**Cause**: Cron job not configured

**Solution**:
```bash
# Verify crontab
crontab -l

# Add if doesn't exist
0 2 * * * cd /path/to/cth && php artisan events:autoclose
```

### Logging and Monitoring

#### Log Files

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Filter by level
grep "ERROR" storage/logs/laravel.log

# View command logs
grep "AutoCloseEvents" storage/logs/laravel.log
```

#### Log Examples

```
[2025-11-06 16:30:00] local.INFO: Starting AutoCloseEvents command...
[2025-11-06 16:30:01] local.INFO: Closing event 12345 for user 67 in team 1.
[2025-11-06 16:30:05] local.INFO: AutoCloseEvents completed. Closed 15 events.

[2025-11-06 16:35:00] local.INFO: Migration: Updated 185 events with new extra hours logic
[2025-11-06 16:35:00] local.INFO: Verification: Found 99 events without event type
```

### Security Monitoring

#### Metrics to Watch

- Failed decryption attempts
- Requests with invalid timestamps
- Suspicious geolocations
- Multiple attempts with incorrect codes
- Anomalous usage patterns

#### Implementation

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

### Backup and Recovery

#### Database Backup

```bash
# Complete backup
mysqldump -u user -p cth > backup_$(date +%Y%m%d).sql

# Backup with compression
mysqldump -u user -p cth | gzip > backup_$(date +%Y%m%d).sql.gz

# Restore backup
mysql -u user -p cth < backup_20251106.sql
```

#### File Backup

```bash
# Storage backup
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Complete application backup
tar -czf cth_backup_$(date +%Y%m%d).tar.gz \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage/logs' \
  /path/to/cth
```

### Performance Optimization

#### Database

```sql
-- Important indexes
CREATE INDEX idx_events_user_date ON events(user_id, start);
CREATE INDEX idx_events_team_date ON events(team_id, start);
CREATE INDEX idx_events_open ON events(is_open);

-- Analyze tables
ANALYZE TABLE events;
ANALYZE TABLE users;
ANALYZE TABLE work_centers;
```

#### Laravel

```bash
# Optimize autoloader
composer dump-autoload --optimize

# Configuration cache
php artisan config:cache

# Route cache
php artisan route:cache

# View cache
php artisan view:cache

# Complete optimization
php artisan optimize
```

#### Web Server

```nginx
# nginx.conf - gzip compression
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript 
           application/x-javascript application/xml+rss 
           application/json application/javascript;

# Static asset cache
location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

---

## 📚 Additional Resources

### Reference Documentation

- **Laravel**: https://laravel.com/docs
- **Livewire**: https://livewire.laravel.com/docs
- **Flutter**: https://flutter.dev/docs
- **Tailwind CSS**: https://tailwindcss.com/docs

### Code Conventions

#### PHP/Laravel

- **PSR-12**: Coding standard
- **Class names**: PascalCase
- **Method names**: camelCase
- **Variable names**: snake_case (DB), camelCase (code)

#### Dart/Flutter

- **Effective Dart**: Official style guide
- **Class names**: PascalCase
- **Method names**: camelCase
- **File names**: snake_case

### Version Control

#### Branching Strategy

```
main (production)
  ├── develop (development)
  │   ├── feature/new-feature
  │   ├── fix/bug-fix
  │   └── hotfix/urgent-fix
```

#### Semantic Commits

```
feat: New feature
fix: Bug fix
docs: Documentation changes
style: Format changes (don't affect code)
refactor: Code refactoring
test: Add or modify tests
chore: Maintenance tasks
```

---

## 📞 Support and Contact

For problems, suggestions, or questions:

1. **Review logs**: `storage/logs/laravel.log`
2. **Run diagnostics**: `php artisan events:verify-and-fix --dry-run`
3. **Consult documentation**: This manual and files in `/docs`
4. **Contact development team**: [Your contact information]

---

**Manual Version**: 1.0  
**Last updated**: November 2025  
**Maintained by**: CTH Development Team

---

✅ **This manual is a living document and should be updated with each significant system change.**
