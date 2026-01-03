# Developer Manual - CTH (Time and Schedule Control)

This document provides a detailed technical guide for developers who wish to maintain or extend the CTH system.

## 📋 Table of Contents

1. [System Architecture](#system-architecture)
2. [Technology Stack](#technology-stack)
3. [Project Structure](#project-structure)
4. [Database and Models](#database-and-models)
5. [Business Logic (Services)](#business-logic-services)
6. [Frontend and Livewire](#frontend-and-livewire)
7. [API and Mobile Application](#api-and-mobile-application)
8. [Artisan Commands](#artisan-commands)
9. [Deployment and Configuration](#deployment-and-configuration)
10. [Security and Auditing](#security-and-auditing)

---

## 1. System Architecture

CTH follows a modular monolithic architecture based on the Laravel framework. The system is designed to be scalable and easy to maintain, using design patterns such as the **Service Pattern** and **Repository Pattern** (where applicable).

### Data Flow
The typical flow of a **Request** in CTH is:
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
The **Schema** is maintained through **Migrations** to ensure consistency between **Development** and **Production** environments.

![Database Diagram](images/db-schema.png)
*Caption: Simplified entity-relationship diagram of the CTH system.*

---

## 5. Business Logic (Services)

### SmartClockInService
This is the heart of the clock-in system. It manages:
- Time margin validation.
- **Token** generation for exceptional clock-ins.
- Duration and overlap calculation.

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
- `php artisan cth:install`: Initial system configuration
- `php artisan cth:sync-holidays`: Holiday import from external APIs

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
*Developer Manual - Version 1.0*
*© 2025 CTH Team*
