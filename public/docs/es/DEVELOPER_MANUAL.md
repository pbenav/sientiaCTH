# Manual del Desarrollador - CTH (Control de Tiempo y Horarios)

Este documento proporciona una guía técnica detallada para desarrolladores que deseen mantener o extender el sistema CTH.

## 📋 Tabla de Contenidos

1. [Licencia y Términos de Uso](#licencia-y-términos-de-uso)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Stack Tecnológico](#stack-tecnológico)
4. [Estructura del Proyecto](#estructura-del-proyecto)
5. [Base de Datos y Modelos](#base-de-datos-y-modelos)
6. [Lógica de Negocio (Services)](#lógica-de-negocio-services)
7. [Frontend y Livewire](#frontend-y-livewire)
8. [API y Aplicación Móvil](#api-y-aplicación-móvil)
9. [Comandos Artisan](#comandos-artisan)
10. [Despliegue y Configuración](#despliegue-y-configuración)
11. [Seguridad y Auditoría](#seguridad-y-auditoría)

---

## 1. Licencia y Términos de Uso

### Software Libre bajo Licencia MIT

CTH (Control de Tiempo y Horarios) es **software libre y gratuito** distribuido bajo la [Licencia MIT](../../../LICENSE).

**Copyright © 2022-2026 pbenav**

#### Permisos

Bajo esta licencia, tienes permitido:
- ✅ **Uso comercial**: Usar el software en proyectos comerciales
- ✅ **Modificación**: Adaptar el código a tus necesidades
- ✅ **Distribución**: Compartir el software original o modificado
- ✅ **Uso privado**: Usar el software para proyectos personales
- ✅ **Sublicenciar**: Incluir el software en proyectos con otras licencias compatibles

#### Condiciones

La única condición es que:
- 📄 Incluyas el aviso de copyright y la licencia MIT en todas las copias o porciones sustanciales del software

#### Limitaciones

- ⚠️ **Sin garantía**: El software se proporciona "tal cual", sin garantías de ningún tipo
- ⚠️ **Sin responsabilidad**: Los autores no se hacen responsables de daños derivados del uso del software

#### Encabezado de Licencia para Archivos de Código

Al crear nuevos archivos PHP, utiliza el siguiente encabezado estándar:

```php
<?php

/**
 * CTH - Control de Tiempo y Horarios
 * 
 * Este archivo es parte de CTH, una plataforma integral de gestión
 * de tiempo y control horario empresarial.
 * 
 * @package     CTH
 * @author      pbenav
 * @copyright   2022-2026 pbenav
 * @license     MIT License
 * @link        https://github.com/pbenav/cth
 * @since       Version 1.0.0
 */
```

Para más detalles, consulta el archivo [LICENSE](../../../LICENSE) en la raíz del proyecto.

---

## 2. Arquitectura del Sistema

CTH sigue una arquitectura monolítica modular basada en el framework Laravel. El sistema está diseñado para ser escalable y fácil de mantener, utilizando patrones de diseño como **Service Pattern** y **Repository Pattern** (donde aplica).

### Flujo de Datos
El flujo típico de una **Request** en CTH es:
1. **Route**: Define el **Endpoint**.
2. **Middleware**: Gestiona la **Auth** y el **Locale**.
3. **Controller / Livewire Component**: Gestiona la lógica de presentación.
4. **Service**: Contiene la lógica de negocio pura.
5. **Model**: Interactúa con la **Database** mediante Eloquent.

---

## 2. Stack Tecnológico

Para mantener la coherencia técnica, se utilizan términos que habitualmente no se traducen al español en el ámbito profesional:

- **Backend**: Laravel 10.x (PHP 8.4+).
- **Frontend**: Livewire, Alpine.js y Tailwind CSS (TALL Stack).
- **Database**: MySQL / MariaDB.
- **Mobile**: Flutter (Dart) con arquitectura BLoC.
- **DevOps**: Docker, Composer, npm.

---

## 3. Estructura del Proyecto

### Directorios Clave
- `app/Http/Controllers`: Controladores para la web y la API.
- `app/Http/Livewire`: Componentes reactivos de la interfaz.
- `app/Models`: Definición de entidades y relaciones.
- `app/Services`: Lógica central (p. ej., `SmartClockInService`).
- `database/migrations`: Definición del **Schema** de la base de datos.
- `database/seeders`: Datos iniciales y de prueba.

---

## 4. Base de Datos y Modelos

### Modelos Principales
- **User**: Gestiona la información del usuario, su **Locale** y su relación con los equipos.
- **Event**: Registra cada **Clock-in**, **Clock-out** o **Pause**.
- **Team**: Implementa la funcionalidad multi-inquilino (multi-tenancy) de Jetstream.
- **WorkCenter**: Define las ubicaciones físicas y sus etiquetas NFC.

### Migrations y Schema
El **Schema** se mantiene mediante **Migrations** para asegurar la consistencia entre entornos de **Development** y **Production**.

![Diagrama de Base de Datos](images/db-schema.png)
*Caption: Diagrama entidad-relación simplificado del sistema CTH.*

---

## 5. Lógica de Negocio (Services)

### SmartClockInService
Este es el corazón del sistema de fichaje. Gestiona:
- Validación de márgenes de tiempo.
- Generación de **Tokens** para fichajes excepcionales.
- Cálculo de duraciones y solapamientos.

---

## 6. Frontend y Livewire

La interfaz de usuario utiliza **Blade** templates enriquecidos con componentes **Livewire**. Esto permite una experiencia de usuario (UX) fluida sin necesidad de una SPA compleja.

### Estilos con Tailwind CSS
Se utiliza **Tailwind CSS** para el diseño responsivo. Los archivos de configuración se encuentran en `tailwind.config.js`.

---

## 7. API y Aplicación Móvil

La aplicación móvil se comunica con el backend mediante una **REST API**.

### Autenticación
Se utiliza **Laravel Sanctum** para la gestión de **Tokens** de API. Cada usuario tiene un **Token** único que debe configurar en su aplicación móvil.

### Endpoints Principales
- `POST /api/login`: Autenticación y obtención de **Token**.
- `GET /api/clock-status`: Estado actual del fichaje del usuario.
- `POST /api/clock-in`: Registro de entrada.

---

## 8. Comandos Artisan

Se han desarrollado comandos personalizados para facilitar el mantenimiento:

### Comandos de Instalación y Configuración
- `php artisan cth:install`: Configuración inicial del sistema
- `php artisan cth:sync-holidays`: Importación de días festivos desde APIs externas

### Comandos de Base de Datos
- `php artisan db:verify-schema`: Verifica la integridad del esquema de base de datos
  - `--fix`: Añade automáticamente las columnas faltantes
  - `--table=nombre`: Verifica solo una tabla específica
  
  **Ejemplo de uso:**
  ```bash
  # Verificar el esquema completo
  php artisan db:verify-schema
  
  # Verificar y reparar automáticamente
  php artisan db:verify-schema --fix
  
  # Verificar solo la tabla users
  php artisan db:verify-schema --table=users
  ```

Este comando es especialmente útil cuando:
- Se actualiza una base de datos en producción
- Faltan columnas añadidas en actualizaciones recientes
- Se necesita verificar la integridad del esquema sin ejecutar migraciones

### Comandos de Permisos
- `php artisan permissions:sync`: Sincroniza la matriz de permisos con la base de datos
- `php artisan permissions:update`: Actualiza permisos y roles del sistema

---

## 9. Despliegue y Configuración

### Archivo .env
La configuración sensible se gestiona mediante el archivo `.env`. Asegúrate de configurar correctamente las variables de **Database**, **Mail** y **App Key**.

### Build y Assets
Para compilar los assets de frontend:
```bash
npm install
npm run build
```

---

## 10. Seguridad y Auditoría

### Seguridad
- **Password Hashing**: Se utiliza Bcrypt por defecto.
- **CSRF Protection**: Habilitada en todos los formularios web.
- **Rate Limiting**: Aplicado a los **Endpoints** de la API para prevenir ataques de fuerza bruta.

### Logs
El sistema utiliza el **Log** de Laravel para registrar errores y eventos críticos en `storage/logs/laravel.log`.

---
*Manual del Desarrollador - Versión 1.0*
*© 2025 CTH Team*
