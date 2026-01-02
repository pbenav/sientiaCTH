# CTH - Control de Tiempo y Horarios

CTH es una solución integral para la gestión del control horario y la productividad empresarial, compuesta por una potente plataforma web (Laravel) y una aplicación móvil (Flutter).

## 🚀 Características Principales

- **SmartClockIn**: Sistema inteligente de fichaje con detección de horarios.
- **Multi-idioma**: Soporte completo para Español e Inglés (UI y Documentación).
- **Gestión de Equipos**: Estructura multi-inquilino para gestionar múltiples departamentos o empresas.
- **Informes Avanzados**: Exportación de datos en PDF, Excel y CSV.
- **Integración Móvil**: Fichaje mediante App con soporte para etiquetas NFC.

## 📚 Documentación

La documentación detallada está disponible en el directorio `public/docs` y es accesible directamente desde la aplicación según el idioma del usuario:

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
- **PHP** ^8.4 (Recomendado)
- **Composer**
- **Node.js & npm**
- **MySQL / MariaDB**

### Pasos de Instalación

1. **Clonar el repositorio y entrar en la carpeta**:
   ```bash
   git clone <url-del-repositorio>
   cd cth
   ```

2. **Instalar dependencias de Backend**:
   ```bash
   composer install
   ```

3. **Instalar dependencias de Frontend**:
   ```bash
   npm install && npm run build
   ```

4. **Configuración del Entorno**:
   Copia el archivo de ejemplo y configura tus credenciales de base de datos:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Base de Datos e Inicialización**:
   El sistema incluye una migración consolidada que crea automáticamente el administrador global (`admin@cth.local` / `password`) y el equipo de bienvenida:
   ```bash
   php artisan migrate --seed
   ```

6. **Servidor de Desarrollo**:
   ```bash
   php artisan serve
   ```

---

## 📱 Aplicación Móvil (Flutter)

El código fuente de la aplicación móvil se encuentra en la carpeta `cth_mobile/`. Para compilar la aplicación:

1. Entra en la carpeta: `cd cth_mobile`
2. Instala dependencias: `flutter pub get`
3. Compila el APK: `flutter build apk`

---

## 🛡️ Seguridad y Licencia

- **Seguridad**: Si descubres alguna vulnerabilidad, por favor abre un *issue* o contacta con el equipo de desarrollo.
- **Licencia**: Este proyecto está bajo la licencia MIT.

---
*© 2025 CTH - Control de Tiempo y Horarios*
