# Developer Manual - sientiaCTH (Time and Schedule Control)

This document provides a detailed technical guide for developers who wish to maintain or extend the sientiaCTH system.

## 📋 Table of Contents

1. [License and Terms of Use](#license-and-terms-of-use)
2. [System Architecture](#system-architecture)
3. [Technology Stack](#technology-stack)
4. [Project Structure](#project-structure)
5. [Database and Models](#database-and-models)
6. [Business Logic (Services)](#business-logic-services)
7. [Frontend and Livewire](#frontend-and-livewire)
8. [API and Mobile Application](#api-and-mobile-application)
9. [Artisan Commands](#artisan-commands)
10. [Deployment and Configuration](#deployment-and-configuration)
11. [Security and Auditing](#security-and-auditing)

---

## 1. License and Terms of Use

### Free Software under MIT License

sientiaCTH (Time and Schedule Control) is **free and open-source software** distributed under the [MIT License](../../../LICENSE).

**Copyright © 2022-2026 pbenav**

#### Permissions

Under this license, you are permitted to:
- ✅ **Commercial use**: Use the software in commercial projects
- ✅ **Modification**: Adapt the code to your needs
- ✅ **Distribution**: Share the original or modified software
- ✅ **Private use**: Use the software for personal projects
- ✅ **Sublicensing**: Include the software in projects with other compatible licenses

#### Conditions

The only condition is that:
- 📄 You include the copyright notice and MIT license in all copies or substantial portions of the software

#### Limitations

- ⚠️ **No warranty**: The software is provided "as is", without warranties of any kind
- ⚠️ **No liability**: The authors are not liable for damages arising from the use of the software

#### License Header for Code Files

When creating new PHP files, use the following standard header:

```php
<?php

/**
 * sientiaCTH - Time and Schedule Control
 * 
 * This file is part of sientiaCTH, a comprehensive time and
 * work schedule management platform.
 * 
 * @package     sientiaCTH
 * @author      pbenav
 * @copyright   2022-2026 pbenav
 * @license     MIT License
 * @link        https://github.com/pbenav/sientiaCTH
 * @since       Version 1.0.0
 */
```

For more details, see the [LICENSE](../../../LICENSE) file in the project root.

> [!TIP]
> **Enjoying the project?** Consider supporting it on [Patreon](https://www.patreon.com/cw/sientiaCTH_ControlHorario) to ensure its continued development.

---

## 2. System Architecture

sientiaCTH follows a modular monolithic architecture based on the Laravel framework. The system is designed to be scalable and easy to maintain, using design patterns such as the **Service Pattern** and **Repository Pattern** (where applicable).

### Data Flow
The typical flow of a **Request** in sientiaCTH is:
1. **Route**: Defines the **Endpoint**.
2. **Middleware**: Manages **Auth** and **Locale**.
3. **Controller / Livewire Component**: Manages presentation logic.
4. **Service**: Contains pure business logic.
5. **Model**: Interacts with the **Database** via Eloquent.

---

## 2. Technology Stack

- **Backend**: Laravel 10.x (PHP 8.4+).
- **Frontend**: Livewire, Alpine.js, and Tailwind CSS (TALL Stack).
- **Database**: MySQL / MariaDB.
- **Mobile**: Flutter (Dart) with BLoC architecture.
- **DevOps**: Docker, Composer, npm.

---

## 3. Project Structure

### Key Directories
- `app/Http/Controllers`: Controllers for web and API.
- `app/Http/Livewire`: Reactive interface components.
- `app/Models`: Entity and relationship definitions.
- `app/Services`: Core logic (e.g., `SmartClockInService`).
- `database/migrations`: Database **Schema** definition.
- `database/seeders`: Initial and test data.

---

## 4. Database and Models

### Main Models
- **User**: Manages user information, their **Locale**, and their relationship with teams.
- **Event**: Records each **Clock-in**, **Clock-out**, or **Pause**.
- **Team**: Implements Jetstream's multi-tenancy functionality.
- **WorkCenter**: Defines physical locations and their NFC tags.

### Migrations and Schema
The **Schema** is maintained through **Migrations** to ensure consistency between environments.

**Idempotency**: All migrations must be designed to be run multiple times without failing. Using `Schema::hasColumn()` and `Schema::hasTable()` is recommended to ensure compatibility with databases that have already been manually modified or through partial updates.

![Database Diagram](images/db-schema.png)
*Caption: Simplified entity-relationship diagram of the sientiaCTH system.*

---

## 5. Business Logic (Services)

### SmartClockInService
This is the heart of the clock-in system. It manages:
- Time margin validation.
- **Token** generation for exceptional clock-ins.
- Duration and overlap calculation.
- **Workday Calculation**: Conversion of net minutes to equivalent days based on user metadata.

### Audit Log & History (InsertHistory Trait)
The auditing system has been optimized for efficiency:
- **Diff Storage**: Only changed fields are stored via attribute comparison.
- **Memory Optimization**: Eagerly loaded relations are removed before model-to-JSON conversion to prevent record bloating.
- **LongText Support**: Database columns are optimized to handle large data volumes.

---

## 6. Frontend and Livewire

The user interface uses **Blade** templates enriched with **Livewire** components. This allows for a smooth user experience (UX) without the need for a complex SPA.

### Styling with Tailwind CSS
**Tailwind CSS** is used for responsive design. Configuration files are located in `tailwind.config.js`.

---

## 7. API and Mobile Application

The mobile application communicates with the backend via a **REST API**.

### Authentication
**Laravel Sanctum** is used for API **Token** management. Each user has a unique **Token** that must be configured in their mobile application.

### Main Endpoints
- `POST /api/login`: Authentication and **Token** retrieval.
- `GET /api/clock-status`: Current user clock-in status.
- `POST /api/clock-in`: Entry registration.

---

## 8. Artisan Commands

Custom commands have been developed to facilitate maintenance:

### Installation and Configuration Commands
- `php artisan sientiaCTH:install`: Initial system configuration
- `php artisan sientiaCTH:sync-holidays`: Holiday import from external APIs

### Database Commands
- `php artisan db:verify-schema`: Verifies database schema integrity
  - `--fix`: Automatically adds missing columns
  - `--table=name`: Verifies only a specific table
  
  **Usage examples:**
  ```bash
  # Verify complete schema
  php artisan db:verify-schema
  
  # Verify and automatically repair
  php artisan db:verify-schema --fix
  
  # Verify only the users table
  php artisan db:verify-schema --table=users
  ```

This command is especially useful when:
- Updating a production database
- Missing columns added in recent updates
- Need to verify schema integrity without running migrations

### Permission Commands
- `php artisan permissions:sync`: Syncs permission matrix with database
- `php artisan permissions:update`: Updates system permissions and roles

---

## 9. Deployment and Configuration

### .env File
Sensitive configuration is managed via the `.env` file. Ensure you correctly configure **Database**, **Mail**, and **App Key** variables.

### Build and Assets
To compile frontend assets:
```bash
npm install
npm run build
```

---

## 10. Security and Auditing

### Security
- **Password Hashing**: Bcrypt is used by default.
- **CSRF Protection**: Enabled on all web forms.
- **Rate Limiting**: Applied to API **Endpoints** to prevent brute force attacks.

### Logs
The system uses Laravel's **Log** to record errors and critical events in `storage/logs/laravel.log`.

---
---

## 💖 Support the Development

If sientiaCTH helps you in your workflow or your company, consider supporting us on Patreon:

👉 **[sientiaCTH on Patreon](https://www.patreon.com/cw/sientiaCTH_ControlHorario)**

---
*Developer Manual - Version 1.0*
*© 2025 sientiaCTH Team*
