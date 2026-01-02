# CTH - Control de Tiempo y Horarios

CTH es una solución integral para la gestión del control horario y la productividad empresarial, compuesta por una potente plataforma web (Laravel) y una aplicación móvil (Flutter).

## 🚀 Características Principales

- **SmartClockIn**: Sistema inteligente de fichaje con detección de horarios.
- **Multi-idioma**: Soporte completo para Español e Inglés (tanto en la interfaz como en la documentación).
- **Gestión de Equipos**: Estructura multi-inquilino para gestionar múltiples departamentos o empresas de forma independiente.
- **Informes Avanzados**: Exportación de datos en formatos PDF, Excel y CSV.
- **Integración Móvil**: Fichaje mediante aplicación móvil con soporte para etiquetas NFC.

## 📚 Documentación

La documentación detallada está disponible en el directorio `public/docs` y se filtra automáticamente en la aplicación según el idioma de preferencia del usuario:

### Español 🇪🇸
- [Manual de Usuario](public/docs/es/USER_MANUAL.md)
- [Manual del Desarrollador](public/docs/es/DEVELOPER_MANUAL.md)
- [Referencia API](public/docs/es/REFERENCIA_API.md)

### English 🇺🇸
- [User Manual](public/docs/en/USER_MANUAL.md)
- [Developer Manual](public/docs/en/DEVELOPER_MANUAL.md)
- [API Reference](public/docs/en/API_REFERENCE.md)

---

## 🛠️ Instalación y Configuración

### Requisitos Previos
- **PHP** ^8.4 (Versión recomendada)
- **Composer** (Gestor de dependencias de PHP)
- **Node.js & npm** (Para la gestión de activos del front-end)
- **MySQL / MariaDB** (Base de datos)

### Pasos de Instalación

1. **Clonar el repositorio y acceder a la carpeta**:
   ```bash
   git clone <url-del-repositorio>
   cd cth
   ```

2. **Instalar dependencias del servidor (Backend)**:
   ```bash
   composer install
   ```

3. **Instalar dependencias de la interfaz (Frontend)**:
   ```bash
   npm install && npm run build
   ```

4. **Configuración del Entorno**:
   Copia el archivo de ejemplo y configura las credenciales de tu base de datos:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Base de Datos e Inicialización**:
   El sistema incluye una migración consolidada que crea automáticamente el administrador global (`admin@cth.local` / `password`) y el equipo de "Bienvenida":
   ```bash
   php artisan migrate --seed
   ```

6. **Ejecutar el servidor de desarrollo**:
   ```bash
   php artisan serve
   ```

---

## 📱 Aplicación Móvil (Flutter)

El código fuente de la aplicación móvil se encuentra en la carpeta `cth_mobile/`. Para generar el paquete de instalación:

1. Acceder a la carpeta: `cd cth_mobile`
2. Instalar dependencias: `flutter pub get`
3. Compilar el paquete (APK): `flutter build apk`

---

## 🛡️ Seguridad y Licencia

- **Seguridad**: Si descubres alguna vulnerabilidad, por favor abre una **incidencia** (issue) o contacta directamente con el equipo de desarrollo.
- **Licencia**: Este proyecto se distribuye bajo la licencia MIT.

---
*© 2026 CTH - Control de Tiempo y Horarios*
