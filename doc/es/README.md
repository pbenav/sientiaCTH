# Documentación de la Aplicación CTH

Bienvenido a la documentación completa del sistema CTH (Control de Tiempo y Horarios).

## Índice de Documentación

### 📚 Documentación Principal

#### Guías de Usuario
- **[Manual de Usuario](manual-usuario.md)** - Guía completa con capturas de pantalla y ejemplos
- **[Guía de Instalación](guia-instalacion.md)** - Instrucciones completas de instalación y configuración
- **Manual de Administrador** - Funciones administrativas y gestión de equipos *(próximamente)*

#### Documentación Técnica
- **[Referencia de Comandos](referencia-comandos.md)** - Comandos de Artisan y utilidades del sistema
- **[Guía de Migraciones](guia-migraciones.md)** - Procedimientos de migración y despliegue en producción
- **Esquemas de Base de Datos** - Estructura de la base de datos y relaciones *(próximamente)*
- **Documentación de API** - Referencia técnica de la API *(próximamente)*

#### Seguridad y Mantenimiento
- **[Auditoría de Seguridad](auditoria-seguridad.md)** - Informes de auditoría y recomendaciones de seguridad
- **[Parches de Seguridad](parches-seguridad.md)** - Historial de correcciones de seguridad aplicadas
- **Políticas de Seguridad** - Mejores prácticas y políticas de seguridad *(próximamente)*

### 🔧 Funcionalidades Principales

#### Sistema de Fichajes
- **Smart Clock-In** - Sistema inteligente de fichaje automático
- **Fichajes Excepcionales** - Gestión de fichajes fuera del horario laboral
- **Eventos de Tiempo** - Creación y gestión de eventos temporales

#### Gestión de Equipos
- **Administración de Usuarios** - Gestión de usuarios y permisos
- **Centros de Trabajo** - Configuración de ubicaciones y horarios
- **Días Festivos** - Importación y gestión de días festivos

#### Reportes y Estadísticas
- **Dashboard** - Panel de control con métricas principales
- **Informes de Tiempo** - Reportes detallados de asistencia
- **Estadísticas de Productividad** - Métricas de rendimiento del equipo

### 🚀 Nuevas Funcionalidades (Noviembre 2025)

#### Mejoras en Modales
- **Modales Rediseñados** - Interfaz optimizada para mejor aprovechamiento del espacio
- **Modales Responsivos** - Diseño adaptativo para diferentes tamaños de pantalla

#### Sistema de Horas Extras
- **Nueva Lógica de Horas Extras** - Solo eventos de tipo "workday" NO son horas extras
- **Recálculo Automático** - Sistema automático de recálculo de horas extras
- **Comandos de Verificación** - Herramientas para verificar y corregir datos

#### Importación de Días Festivos
- **Importación Masiva** - Opción "Importar Todo" para días festivos
- **Selección Múltiple** - Checkbox "Seleccionar Todo" mejorado
- **API de Días Festivos** - Integración con servicios externos

## 📝 Historial de Cambios

### Versión Actual (Noviembre 2025)
- ✅ Corrección de descripciones nulas en SmartClockIn
- ✅ Campo de descripción en modales de eventos
- ✅ Nueva lógica de horas extras implementada
- ✅ Migraciones de producción con manejo robusto de errores
- ✅ Comandos de consola documentados
- ✅ Rediseño de modales para mejor UX
- ✅ Funcionalidad "Importar Todo" en días festivos

## 🛠️ Para Desarrolladores

### Comandos Disponibles
```bash
# Comandos de gestión de eventos
php artisan events:autoclose
php artisan events:fix-data
php artisan events:update-extra-hours
php artisan events:verify-and-fix

# Limpieza de cachés
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Estructura de la Aplicación
- **Backend**: Laravel 11 con Livewire
- **Frontend**: Tailwind CSS + Alpine.js
- **Base de Datos**: MySQL/MariaDB
- **Autenticación**: Laravel Jetstream
- **Tiempo Real**: Livewire para interactividad

## 📞 Soporte

Para dudas o problemas:
1. Consulta esta documentación
2. Revisa los logs de la aplicación
3. Utiliza los comandos de verificación disponibles

---

*Última actualización: 6 de noviembre de 2025*