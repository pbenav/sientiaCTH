# 📡 CTH Mobile API Reference v1

**Version**: 1.0  
**Base URL**: `/api/v1`  
**Authentication**: Custom Header / Bearer Token (depending on endpoint)

---

## 📋 Table of Contents

1. [Introduction](#introduction)
2. [Conventions](#conventions)
3. [Authentication](#authentication)
4. [Clock & Status](#clock--status)
5. [Team & Profile](#team--profile)
6. [History & Schedule](#history--schedule)
7. [Worker Information](#worker-information)

---

## 🎯 Introduction

This API provides the necessary endpoints for the CTH mobile application to interact with the backend.  
Starting from **November 2025**, the API has been refactored to use cleaner URLs, removing the `/mobile` prefix.  
Legacy prefixed routes (`/api/v1/mobile/...`) are still supported for backward compatibility but are deprecated.

---

## 📝 Conventions

- **Date Format**: ISO 8601 (`YYYY-MM-DD` or `YYYY-MM-DDTHH:mm:ssZ`)
- **Responses**: JSON format
- **HTTP Status Codes**:
  - `200 OK`: Successful request
  - `400 Bad Request`: Validation error
  - `401 Unauthorized`: Invalid credentials
  - `403 Forbidden`: Permission denied
  - `404 Not Found`: Resource not found
  - `500 Internal Server Error`: Server error

---

## 🔐 Authentication

Most endpoints require identifying the user and the work center.

**Common Headers**:
- `Content-Type`: `application/json`
- `Accept`: `application/json`

**Body Params (often required)**:
- `user_secret_code`: The user's secret access code.
- `work_center_code`: The code of the work center the user is at.

---

## ⏰ Clock & Status

### 1. Clock Action (Clock In/Out/Pause)

Performs a clocking action based on the user's current status.

- **Endpoint**: `POST /clock`
- **Description**: Automatically determines the next action (Start Work, Pause, Resume, Finish) based on current state.

**Request Body**:
```json
{
  "user_secret_code": "1234",
  "work_center_code": "WC-001",
  "location": {
    "latitude": 40.4168,
    "longitude": -3.7038
  }
}
```

**Response (200 OK)**:
```json
{
  "success": true,
  "action_taken": "clock_in",
  "message": "Successfully clocked in",
  "data": {
    "user": { ... },
    "current_status": "working",
    "today_records": [ ... ]
  }
}
```

### 2. Get Current Status

Retrieves the current status of the user without performing any action.

- **Endpoint**: `POST /status`

**Request Body**:
```json
{
  "user_secret_code": "1234",
  "work_center_code": "WC-001"
}
```

**Response (200 OK)**:
```json
{
  "status": "working", // or "clocked_out", "on_break"
  "user": { ... },
  "stats": {
       "today_time": "04:30",
       "week_time": "20:00"
  }
}
```

### 3. Sync Offline Data

Syncs clocking records stored locally while offline.

- **Endpoint**: `POST /sync`

**Request Body**:
```json
{
  "user_secret_code": "1234",
  "work_center_code": "WC-001",
  "pending_actions": [
    {
      "action": "clock_in",
      "timestamp": "2023-10-27T08:00:00Z",
      "location": { ... }
    }
  ]
}
```

---

## 👥 Team & Profile

### 4. Switch Team

Switches the user's current active team context.

- **Endpoint**: `POST /team/switch`
- **Description**: Updates `current_team_id` for the user. Used when a user belongs to multiple teams/work centers.

**Request Body**:
```json
{
  "user_code": "1234",
  "work_center_code": "WC-002" // The work center belonging to the target team
}
```

**Response (200 OK)**:
```json
{
  "success": true,
  "message": "Team switched successfully",
  "data": {
      "current_team_id": 5,
      "current_team_name": "New Team Name",
      "status": "..."
  }
}
```

### 5. Update Profile

Updates user profile settings (e.g., app preferences).

- **Endpoint**: `POST /profile/update`

**Request Body**:
```json
{
  "user_id": 123,
  "preferences": {
      "theme": "dark",
      "notifications_enabled": true
  }
}
```

---

## 📅 History & Schedule

### 6. Get History

Retrieves past clocking events.

- **Endpoint**: `POST /history`

**Request Body**:
```json
{
  "user_secret_code": "1234",
  "work_center_code": "WC-001",
  "month": 10,
  "year": 2025
}
```

### 7. Get Schedule

Retrieves the work schedule.

- **Endpoint**: `POST /schedule`

**Request Body**:
```json
{
  "user_secret_code": "1234",
  "work_center_code": "WC-001"
}
```

### 8. Update Schedule

Updates specific schedule slots (requires admin/manager permissions or specific logic).

- **Endpoint**: `POST /schedule/update`

---

## 👤 Worker Information

### 9. Get Worker Data

Retrieves public information about a worker by their code.

- **Endpoint**: `GET /worker/{code}`

**Response (200 OK)**:
```json
{
    "id": 1,
    "name": "Jane",
    "family_name1": "Doe",
    "photo_url": "..."
}
```

---

## 🔐 Permission & Role System

### System Roles

CTH implements a granular permission system with the following roles:

- **Administrator**: Full team control and configuration
- **Editor**: User permissions + announcement management
- **User**: Standard access for clocking and queries
- **Inspector**: Read-only access for auditing

### Key Permissions

- `teams.limits.manage`: Manage team creation limit per member
- `announcements.create/update/delete`: Announcement management (Admin and Editor)
- `events.view.team`: View team events
- `events.create.team`: Create events for other members (Admin)

**Note**: The mobile API automatically validates permissions based on the user's role in the active team context.

---

## 💖 Support the Project

👉 **[Support on Patreon](https://www.patreon.com/cw/CTH_ControlHorario)**
