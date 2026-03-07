# 📡 Referencia de API Móvil CTH v1

**Versión**: 1.0  
**URL Base**: `/api/v1`  
**Autenticación**: Header Personalizado / Token Bearer (según endpoint)

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Convenciones](#convenciones)
3. [Autenticación](#autenticación)
4. [Fichaje y Estado](#fichaje-y-estado)
5. [Equipo y Perfil](#equipo-y-perfil)
6. [Historial y Horario](#historial-y-horario)
7. [Información del Trabajador](#información-del-trabajador)

---

## 🎯 Introducción

Esta API proporciona los endpoints necesarios para que la aplicación móvil CTH interactúe con el backend.  
A partir de **Noviembre 2025**, la API ha sido refactorizada para usar URLs más limpias, eliminando el prefijo `/mobile`.  
Las rutas antiguas con prefijo (`/api/v1/mobile/...`) siguen siendo soportadas por compatibilidad pero están obsoletas.

---

## 📝 Convenciones

- **Formato de Fecha**: ISO 8601 (`YYYY-MM-DD` o `YYYY-MM-DDTHH:mm:ssZ`)
- **Respuestas**: Formato JSON
- **Códigos de Estado HTTP**:
  - `200 OK`: Petición exitosa
  - `400 Bad Request`: Error de validación
  - `401 Unauthorized`: Credenciales inválidas
  - `403 Forbidden`: Permiso denegado
  - `404 Not Found`: Recurso no encontrado
  - `500 Internal Server Error`: Error del servidor

---

## 🔐 Autenticación

La mayoría de los endpoints requieren identificar al usuario y al centro de trabajo.

**Headers Comunes**:
- `Content-Type`: `application/json`
- `Accept`: `application/json`

**Parámetros de Body (frecuentemente requeridos)**:
- `user_secret_code`: El código de acceso secreto del usuario.
- `work_center_code`: El código del centro de trabajo donde está el usuario.

---

## ⏰ Fichaje y Estado

### 1. Acción de Fichaje (Entrada/Salida/Pausa)

Realiza una acción de fichaje basada en el estado actual del usuario.

- **Endpoint**: `POST /clock`
- **Descripción**: Determina automáticamente la siguiente acción (Iniciar Jornada, Pausar, Reanudar, Finalizar) basada en el estado actual.

**Cuerpo de la Petición**:
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

**Respuesta (200 OK)**:
```json
{
  "success": true,
  "action_taken": "clock_in", // fichar_entrada
  "message": "Jornada iniciada correctamente",
  "data": {
    "user": { ... },
    "current_status": "working",
    "today_records": [ ... ]
  }
}
```

### 2. Obtener Estado Actual

Obtiene el estado actual del usuario sin realizar ninguna acción.

- **Endpoint**: `POST /status`

**Cuerpo de la Petición**:
```json
{
  "user_secret_code": "1234",
  "work_center_code": "WC-001"
}
```

**Respuesta (200 OK)**:
```json
{
  "status": "working", // o "clocked_out", "on_break"
  "user": { ... },
  "stats": {
       "today_time": "04:30",
       "week_time": "20:00"
  }
}
```

### 3. Sincronizar Datos Offline

Sincroniza registros de fichaje almacenados localmente mientras se estaba sin conexión.

- **Endpoint**: `POST /sync`

**Cuerpo de la Petición**:
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

## 👥 Equipo y Perfil

### 4. Cambiar de Equipo

Cambia el contexto de equipo activo del usuario.

- **Endpoint**: `POST /team/switch`
- **Descripción**: Actualiza el `current_team_id` del usuario. Se usa cuando un usuario pertenece a múltiples equipos/centros de trabajo.

**Cuerpo de la Petición**:
```json
{
  "user_code": "1234",
  "work_center_code": "WC-002" // El centro de trabajo perteneciente al equipo destino
}
```

**Respuesta (200 OK)**:
```json
{
  "success": true,
  "message": "Equipo cambiado correctamente",
  "data": {
      "current_team_id": 5,
      "current_team_name": "Nuevo Nombre de Equipo",
      "status": "..."
  }
}
```

### 5. Actualizar Perfil

Actualiza configuraciones del perfil de usuario (ej: preferencias de app).

- **Endpoint**: `POST /profile/update`

**Cuerpo de la Petición**:
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

## 📅 Historial y Horario

### 6. Obtener Historial

Obtiene eventos de fichaje pasados.

- **Endpoint**: `POST /history`

**Cuerpo de la Petición**:
```json
{
  "user_secret_code": "1234",
  "work_center_code": "WC-001",
  "month": 10,
  "year": 2025
}
```

### 7. Obtener Horario

Obtiene el horario laboral.

- **Endpoint**: `POST /schedule`

**Cuerpo de la Petición**:
```json
{
  "user_secret_code": "1234",
  "work_center_code": "WC-001"
}
```

### 8. Actualizar Horario

Actualiza tramos horarios específicos (requiere permisos de admin/manager o lógica específica).

- **Endpoint**: `POST /schedule/update`

---

## 👤 Información del Trabajador

### 9. Obtener Datos del Trabajador

Obtiene información pública sobre un trabajador mediante su código.

- **Endpoint**: `GET /worker/{code}`

**Respuesta (200 OK)**:
```json
{
    "id": 1,
    "name": "Jane",
    "family_name1": "Doe",
    "photo_url": "..."
}
```

---

## 🔐 Sistema de Permisos y Roles

### Roles del Sistema

CTH implementa un sistema de permisos granular con los siguientes roles:

- **Administrador**: Control total del equipo y configuración
- **Editor**: Permisos de usuario + gestión de anuncios
- **Usuario**: Acceso estándar para fichaje y consultas
- **Inspector**: Solo lectura para auditorías

### Permisos Clave

- `teams.limits.manage`: Gestionar límite de equipos por miembro
- `announcements.create/update/delete`: Gestión de anuncios (Admin y Editor)
- `events.view.team`: Ver eventos del equipo
- `events.create.team`: Crear eventos para otros miembros (Admin)

**Nota**: La API móvil valida permisos automáticamente según el rol del usuario en el contexto del equipo activo.

---

## 💖 Apoya el Proyecto

👉 **[Apoyar en Patreon](https://www.patreon.com/cw/CTH_ControlHorario)**
