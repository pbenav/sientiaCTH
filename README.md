# CTH - Control de Tiempo y Horarios

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/pbenav/cth/releases)
[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)](https://php.net)

[🇬🇧 English Version](README.en.md)

CTH es una solución integral de código abierto para la gestión del control horario y la productividad empresarial, compuesta por una potente plataforma web (Laravel + Livewire) y una aplicación móvil multiplataforma (Flutter).

## ✨ Características Destacadas

### 🎯 Gestión de Tiempo
- **SmartClockIn**: Sistema inteligente que detecta automáticamente la siguiente acción (entrada, pausa, salida)
- **Horarios Flexibles**: Soporte para turnos, períodos de gracia y validación de tramos
- **Fichaje Excepcional**: Tokens seguros para fichajes fuera del horario habitual
- **Sistema de Pausas**: Gestión completa de descansos dentro de la jornada laboral

### 👥 Gestión Empresarial
- **Multi-equipo**: Arquitectura multi-tenant para gestionar múltiples departamentos o empresas
- **Sistema de Permisos**: 60+ permisos granulares con 4 roles predefinidos
- **Centros de Trabajo**: Gestión de múltiples ubicaciones con geolocalización
- **Gestión de Festivos**: Calendario automático con API de festivos oficiales

### 📊 Reportes y Analytics
- **Informes Profesionales**: Exportación en PDF (Browsershot), Excel y CSV
- **Dashboard Interactivo**: Estadísticas en tiempo real con Chart.js
- **KPIs Visuales**: Indicadores de rendimiento y horas trabajadas vs. programadas
- **Historial Completo**: Filtrado avanzado de fichajes y eventos

### 📱 Aplicación Móvil
- **Flutter Multiplataforma**: Apps nativas para Android e iOS
- **Fichaje NFC**: Soporte para etiquetas NFC en centros de trabajo
- **API RESTful Completa**: 15+ endpoints con autenticación Sanctum
- **Sincronización Offline**: Funcionamiento sin conexión

### 🌍 Internacionalización
- **Multi-idioma**: Soporte completo para Español e Inglés
- **Documentación Bilingüe**: Manuales técnicos en ES/EN
- **Localización Regional**: Formatos de fecha, hora y moneda adaptados

## 📚 Documentación

La documentación detallada está disponible en el directorio `public/docs` y se filtra automáticamente en la aplicación según el idioma de preferencia del usuario:

### Español 🇪🇸
- [Manual de Usuario](public/docs/es/USER_MANUAL.md) - Guía completa para usuarios finales
- [Manual del Desarrollador](public/docs/es/DEVELOPER_MANUAL.md) - Documentación técnica para desarrolladores
- [Referencia API](public/docs/es/REFERENCIA_API.md) - Endpoints de la API móvil
- [Registro de Cambios](public/docs/es/CHANGELOG.md) - Historial completo del proyecto

### English 🇺🇸
- [User Manual](public/docs/en/USER_MANUAL.md) - Complete guide for end users
- [Developer Manual](public/docs/en/DEVELOPER_MANUAL.md) - Technical documentation for developers
- [API Reference](public/docs/en/API_REFERENCE.md) - Mobile API endpoints
- [Changelog](public/docs/en/CHANGELOG.md) - Complete project history

### Technical Documentation 🔧
- [Permission System Analysis](public/docs/technical/PERMISSION_SYSTEM_ANALYSIS.md) - Deep dive into permissions architecture
- [Permission System Guide](public/docs/technical/PERMISSION_SYSTEM_GUIDE.md) - Developer guide for permissions
- [Performance Optimization](public/docs/technical/PERFORMANCE_OPTIMIZATION.md) - Performance improvements and benchmarks

---

## 🛠️ Instalación y Configuración

### Requisitos del Sistema
- **PHP** ^8.1 (recomendado 8.2+)
- **Composer** 2.x
- **Node.js** 18.x o superior
- **npm** o **yarn**
- **MySQL** 8.0+ / **MariaDB** 10.5+
- **Extensiones PHP**: PDO, Mbstring, OpenSSL, Tokenizer, XML, Ctype, JSON, BCMath, GD

### Requisitos Opcionales
- **Node.js** con **Puppeteer** para generación de PDFs de alta calidad
- **Redis** para caché y colas (mejora el rendimiento)
- **Supervisor** para workers de colas en producción

### Pasos de Instalación

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/pbenav/cth.git
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

## 🔒 Seguridad

CTH implementa múltiples capas de seguridad:

- **Autenticación robusta**: Laravel Sanctum con soporte 2FA opcional
- **Protección CSRF**: En todos los formularios
- **Sanitización HTML**: HTMLPurifier para contenido de usuarios
- **Rate Limiting**: Protección contra ataques de fuerza bruta
- **Auditoría completa**: Registro de acciones críticas
- **Tokens seguros**: Para fichajes excepcionales y API

### Reportar Vulnerabilidades

Si encuentras una vulnerabilidad de seguridad, por favor **NO** abras un issue público. Contacta directamente con el mantenedor del proyecto.

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add: nueva funcionalidad increíble'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guías de Contribución

- Sigue el estilo de código existente (PSR-12 para PHP)
- Escribe tests para nuevas funcionalidades
- Actualiza la documentación según sea necesario
- Asegúrate de que todos los tests pasen antes de enviar el PR

---

## 👨‍💻 Autor

**Pablo Benavides** ([@pbenav](https://github.com/pbenav))

---

## 🙏 Agradecimientos

- [Laravel](https://laravel.com) - El framework PHP que hace posible este proyecto
- [Livewire](https://laravel-livewire.com) - Para componentes reactivos sin JavaScript
- [Tailwind CSS](https://tailwindcss.com) - Framework CSS utility-first
- [FullCalendar](https://fullcalendar.io) - Librería de calendario interactivo
- [Flutter](https://flutter.dev) - Para la aplicación móvil multiplataforma
- Comunidad de código abierto por sus increíbles herramientas

---

## 📊 Estado del Proyecto

- ✅ **Versión estable**: 1.0.0
- ✅ **Producción**: Sistema probado en entornos reales
- ✅ **Mantenimiento activo**: Actualizaciones y correcciones regulares
- 🚀 **En desarrollo**: Nuevas funcionalidades en el roadmap

Para ver el historial completo de cambios, consulta el [CHANGELOG](public/docs/es/CHANGELOG.md).

---

## 📞 Soporte

- **Documentación**: Revisa los manuales en `public/docs/`
- **Issues**: Reporta bugs o solicita features en [GitHub Issues](https://github.com/pbenav/cth/issues)
- **Discusiones**: Únete a las [Discussions](https://github.com/pbenav/cth/discussions) para preguntas generales

---

**⭐ Si este proyecto te resulta útil, considera darle una estrella en GitHub**

---

## 📄 Licencia

CTH es **software libre y gratuito** distribuido bajo la [Licencia MIT](LICENSE).

Esto significa que puedes:
- ✅ **Usar** el software para cualquier propósito (personal o comercial)
- ✅ **Modificar** el código según tus necesidades
- ✅ **Distribuir** copias del software
- ✅ **Sublicenciar** y vender copias del software modificado

La única condición es que incluyas el aviso de copyright y la licencia MIT en todas las copias o porciones sustanciales del software.

**Autor**: pbenav (2022-2026)  
**Licencia completa**: Ver archivo [LICENSE](LICENSE)

---

## 🔒 Seguridad

Si descubres alguna vulnerabilidad de seguridad, por favor **NO** la publiques en GitHub Issues. En su lugar, contacta directamente con el equipo de desarrollo para que pueda ser atendida de forma responsable.

---

*© 2022-2026 pbenav - CTH es software libre bajo licencia MIT*
