# Copilot Instructions for CTH & CTH Mobile

## Arquitectura General

- El proyecto está dividido en dos partes principales:
  - `cth/`: Aplicación web basada en Laravel para control de horarios y gestión empresarial.
  - `cth_mobile/`: Aplicación móvil Flutter para interacción de usuarios en dispositivos móviles.

## Flujos de Trabajo Clave

### CTH (Laravel)
- **Instalación y setup:**
  1. Instala PHP (>=8.1), Composer, Node y npm.
  2. Ejecuta `composer update` y `npm install && npm run dev`.
  3. Copia `.env.example` a `.env` y configura la base de datos.
  4. Genera la clave con `php artisan key:generate`.
  5. Ejecuta migraciones con `php artisan migrate`.
  6. Opcional: `php artisan migrate:refresh --seed` para datos de prueba.
  7. Inicia el servidor con `php artisan serve`.
- **Scripts útiles:**
  - `update_dev.sh` y `update_prod.sh` para optimización y despliegue.
- **Estructura de código:**
  - Lógica principal en `app/` (Controllers, Models, Services, etc).
  - Configuración en `config/`.
  - Rutas en `routes/`.
  - Pruebas en `tests/`.

### CTH Mobile (Flutter)
- **Compilación:**
  - Usa la tarea "Compilar Flutter app" (`flutter build apk`) para generar el APK.
- **Estructura de código:**
  - Código fuente en `lib/` (screens, blocs, models, widgets, services, utils).
  - Recursos en `assets/`.
  - Configuración en `pubspec.yaml`.
- **Pruebas:**
  - Pruebas de widgets en `test/widget_test.dart`.

## Convenciones y Patrones
- **Laravel:**
  - Sigue la estructura MVC estándar.
  - Usa migraciones y seeders para la gestión de datos.
  - Los servicios y lógica de negocio suelen estar en `app/Services/`.
- **Flutter:**
  - Organización modular por feature en subcarpetas de `lib/`.
  - Uso de blocs para gestión de estado.

## Integraciones y Comunicación
- **API REST:**
  - La app móvil se comunica con la API Laravel vía HTTP.
  - Endpoints definidos en los controladores de `app/Http/Controllers/Mobile/`.
- **Dependencias externas:**
  - Laravel: Composer y npm para dependencias.
  - Flutter: Paquetes definidos en `pubspec.yaml`.

## Ejemplo de flujo de desarrollo
1. Modifica el backend en `cth/app/` y actualiza migraciones si es necesario.
2. Compila y prueba la app móvil usando la tarea de build.
3. Sincroniza cambios de API entre backend y móvil.

## Archivos clave
- `cth/README.md`: Guía de instalación y comandos principales.
- `cth_mobile/README.md`: Referencias y recursos Flutter.
- `cth/app/Http/Controllers/Mobile/`: Endpoints para la app móvil.
- `cth_mobile/lib/`: Código fuente principal de la app Flutter.

---

Actualiza este archivo si cambian los flujos, convenciones o integraciones relevantes.