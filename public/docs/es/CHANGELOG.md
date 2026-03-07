# 📜 Registro de Cambios - CTH

## Resumen Histórico

Este documento recoge los hitos más importantes en la evolución de **CTH (Control de Tiempo y Horarios)**, desde su creación en mayo de 2022 hasta la actualidad.

---

---

## 🎖️ Febrero 2026 (v1.1.0) - Informes Profesionales y Optimización de Auditoría

### Sistema de Informes Profesional
- **Huso Horario Local**: Corrección integral del desfase de horas; los informes reflejan ahora la zona horaria real del equipo/usuario (`config('app.timezone')` como fallback).
- **Días Equivalentes**: Implementación del cálculo de jornadas trabajadas en base a la configuración de `work_schedule` (jornada laboral teórica).
- **Nombres y DNI**: Unificación del formato a `DNI - Apellidos, Nombre` en todos los listados y cabeceras de PDF.
- **Cabeceras Dinámicas**: Inclusión de la fecha y hora de emisión del informe y saltos de página por trabajador.
- **Simplificación de Métricas**: Rediseño de los totales para mostrar duraciones limpias (ej. `139H (~15 días)`) eliminando redundancias.

### Usuarios y Gestión
- **Campo DNI/NIE**: Integración de identificación oficial en el modelo `User` y en la interfaz de gestión de **Filament**.
- **Ordenación Alfabética**: Los selectores y tablas ahora ordenan consistentemente por el primer apellido del trabajador.
- **Persistencia de Centro de Trabajo**: Los centros de trabajo favoritos ahora se guardan asociados al ID del equipo, evitando reseteos al cambiar de perfil.

### Ingeniería y Rendimiento
- **Auditoría Optimizada**: Refactorización del trait `InsertHistory` para almacenar únicamente las diferencias (diffs) de los modelos, reduciendo drásticamente el tamaño en base de datos.
- **Seguridad en Almacenamiento**: Migración de columnas de historial a `LONGTEXT` y eliminación de relaciones Eloquent innecesarias en el log de auditoría.
- **Migraciones Idempotentes**: Todas las nuevas actualizaciones de base de datos son ahora seguras de re-ejecutar.

---

## 🛡️ Febrero 2026 - Mejoras de UX y Control de Jornada (v1.0.1)


---

## 🎓 Enero 2026 - Sistema de Permisos v1.0 y Documentación Integral

### Sistema de Permisos Granular
- **PermissionMatrix**: Matriz centralizada con más de 60 permisos definidos
- **Roles del Sistema**: Administrador, Editor, Usuario e Inspector
- **Permisos Contextuales**: Validación por equipo y recurso específico
- **Límites de Equipos**: Control de cuántos equipos puede crear cada miembro (`teams.limits.manage`)
- **Migración Idempotente**: Sistema seguro para actualizar bases de datos existentes

### Documentación y Localización
- **Visor de Documentación**: Sistema navegable integrado en la aplicación
- **Manuales Consolidados**: Usuario, Desarrollador, API Móvil, Migraciones
- **Soporte Multiidioma**: Documentación completa en español e inglés
- **Filtrado por Idioma**: Contenido adaptado automáticamente al idioma del usuario
- **Enlaces Internos**: Navegación fluida entre documentos con anchors

### Optimización y Rendimiento
- **Consolidación de Migraciones**: Schema inicial unificado para instalaciones limpias
- **Índices Estratégicos**: Optimización de consultas frecuentes
- **Equipo de Bienvenida**: Team inicial automático para nuevos usuarios
- **Null-Safety**: Mejoras en componentes Livewire para prevenir errores

---

## 📱 Diciembre 2025 - Reportes Profesionales y Dashboard Mejorado

### Sistema de Reportes Avanzado
- **Generación de PDF con Browsershot**: PDFs de alta calidad usando Puppeteer/Chromium
  - Numeración automática de páginas
  - Diseño landscape optimizado
  - Metadatos completos (totales, promedios, resúmenes)
- **Motor Alternativo mPDF**: Fallback para entornos sin Node.js
- **Exportación Múltiple**: Excel, CSV y PDF
- **Traducciones Específicas**: Namespace dedicado para informes (`reports.`)
- **Indicadores de Carga**: SweetAlert durante generación de informes

### Dashboard de Control
- **Panel Unificado**: Estadísticas clave en vista consolidada
- **Resumen de Inbox**: Mensajes pendientes y recientes
- **Anuncios Acordeón**: Visualización compacta de avisos del equipo
- **Mensajes Difusión**: Botón "Mensaje a Todos" para comunicaciones masivas
- **KPIs Visuales**: Indicadores de rendimiento en tiempo real

### Sistema de Anuncios
- **Editor Enriquecido**: Soporte Markdown con vista previa
- **Publicación Programada**: Fechas de inicio/fin configurables
- **Permisos Granulares**: Control de creación/edición según rol
- **Sanitización HTML**: HTMLPurifier con CSS personalizado

### Mejoras de Vistas
- **Rediseño de Eventos**: Interfaz renovada con Tailwind CSS y Jetstream
- **Responsive Mejorado**: Adaptación completa para móviles
- **Calendario Optimizado**: Altura viewport y auto-scroll según horario

---

## 🚀 Noviembre 2025 - SmartClockIn y API Móvil Completa

### SmartClockIn
- **Fichaje Inteligente**: Botón que detecta automáticamente la siguiente acción
  - Inicio de jornada
  - Pausa/Reanudación
  - Fin de jornada
  - Fichaje excepcional (fuera de horario)
- **Validación de Horarios**: Integración con horarios laborales del usuario
- **Sistema de Pausas**: Gestión completa de descansos (`pause_event_id`)
- **Períodos de Gracia**: Flexibilidad configurable en entrada/salida

### API Móvil RESTful
- **Endpoints Principales**:
  - `POST /api/v1/clock` - Fichaje inteligente con localización
  - `POST /api/v1/status` - Estado actual y próxima acción
  - `POST /api/v1/history` - Historial de fichajes con filtros
  - `GET /api/v1/schedule` - Horarios de trabajo del usuario
  - `POST /api/v1/sync` - Sincronización offline
- **Autenticación Simplificada**: Login con `user_code` único
- **Sistema NFC**: Verificación de etiquetas NFC en centros de trabajo
- **Configuración Dinámica**: Descarga automática de parámetros del servidor
- **Formato ISO 8601**: Estandarización de días de la semana (1-7)

### Internacionalización
- **Códigos de Estado**: Mensajes localizables en respuestas API
- **Traducciones Completas**: ES/EN en toda la aplicación
- **Preferencia de Idioma**: Configuración por usuario (`locale`)

### Mejoras de UX
- **Rediseño Mobile**: Interfaz optimizada para dispositivos móviles
- **Auto-scroll Calendario**: Posicionamiento inteligente según horario laboral
- **Iconos Consistentes**: Diseño unificado de botones y navegación
- **Tooltips Informativos**: Ayuda contextual en elementos clave

---

## 📊 Octubre 2025 - Centros de Trabajo y Seguridad

### Centros de Trabajo
- **Gestión Completa**: CRUD de ubicaciones de trabajo
- **Asociación con Equipos**: Cada centro pertenece a un equipo
- **Dirección Estructurada**: Campos detallados (ciudad, código postal, país)
- **Centro Predeterminado**: Preferencia por usuario
- **Integración con Fichajes**: Registro de ubicación en cada evento

### Gestión de Festivos
- **Calendario por Equipo**: Festivos independientes por team
- **Importación Automática**: API externa para festivos oficiales españoles
- **Tipos de Festivo**: Clasificación (nacional, regional, local)
- **Visualización en Calendario**: Integración con FullCalendar (color naranja)

### Sistema de Mensajería
- **Mensajes Internos**: Comunicación entre usuarios
- **Hilos de Conversación**: Respuestas anidadas (`parent_id`)
- **Notificaciones en Tiempo Real**: Alertas de nuevos mensajes
- **Gestión de Bandeja**: Marcar como leído, eliminar, archivar
- **Mensajes Masivos**: Difusión a todo el equipo

### Fichaje Excepcional
- **Tokens de Un Solo Uso**: Generación segura para fichajes fuera de horario
- **Validación Temporal**: Tokens con expiración configurable
- **Notificaciones Admin**: Alertas de fichajes excepcionales
- **Modal Específico**: Interfaz dedicada para crear excepcionales

### Seguridad
- **Login Avanzado**: Protección anti brute-force con bloqueos progresivos
- **Auditoría Completa**: Registro de acciones críticas
- **Períodos de Gracia**: Configuración de tolerancia en fichajes
- **Cierre Automático**: Comando `AutoCloseEvents` para eventos abiertos
- **Actualización Laravel 10**: Migración a versión LTS con mejoras de seguridad

### Zonas Horarias
- **Timezone por Equipo**: Cada team puede definir su zona horaria
- **Conversión Automática**: Cálculos correctos independiente del servidor
- **Validación de Tramos**: Soporte para turnos que cruzan medianoche

---

## 🎯 Septiembre 2025 - Fundación de la Era Moderna

### Características Iniciales
- **Sistema de Fichajes**: Implementación del sistema básico de entrada/salida
- **Eventos Todo el Día**: Soporte para eventos de jornada completa (`is_all_day`)
- **Calendario Interactivo**: Integración de FullCalendar con localización en español
- **Gestión de Equipos**: Sistema multiequipo con roles básicos (Owner, Admin, Member, Inspector)
- **Tipos de Evento**: Sistema de clasificación de eventos con códigos de color
- **Estadísticas**: Panel de estadísticas en tiempo real con Chart.js

### Tipos de Evento Base
- Jornada Laboral (verde)
- Vacaciones (azul, autorizable)
- Asuntos Propios (púrpura, autorizable)
- Pausa (naranja)
- Evento Especial (rojo)

### Mejoras de UX/UI
- Modales reactivos con Livewire para crear/editar eventos
- Sistema de validación de formularios en tiempo real
- Interfaz responsive optimizada para móviles
- Localización completa al español de España

---

## 📈 2023-2024 - Evolución y Refinamiento

### Año 2024: Consolidación
- **Optimización de Queries**: Refactorización de consultas para mejor rendimiento
- **Sistema de Roles Avanzado**: Permisos contextuales por equipo
- **Filtrado Mejorado**: Componentes reactivos para búsqueda de eventos y usuarios
- **Exportación Excel**: Integración de Laravel Excel para reportes
- **Validación de Datos**: Mejoras en formularios con validación en tiempo real
- **Internacionalización**: Expansión del sistema de traducciones

### Año 2023: Expansión de Funcionalidades
- **Dashboard con Gráficos**: Integración de Chart.js para estadísticas visuales
- **Reportes Dinámicos**: Sistema flexible de generación de informes
- **Filtros GetTimeRegisters**: Componente reutilizable para filtrado de fichajes
- **Observaciones en Eventos**: Campo de texto libre para notas adicionales
- **Mejoras GDPR**: Cumplimiento con normativa de protección de datos
- **Exportación CSV**: Formato alternativo para análisis de datos
- **Búsqueda por Código de Usuario**: Filtrado rápido con `user_code`
- **Optimización de Caché**: Reducción de consultas redundantes

---

## 🏗️ 2022 - Fundación del Proyecto

### Mayo - Junio 2022: Los Primeros Pasos
- **Commit Inicial** (1 de mayo de 2022): Estructura base con Laravel
- **Sistema de Eventos**: Primera implementación de registro de entrada/salida
- **Interfaz Numpad**: Teclado numérico para fichaje rápido con código de usuario
- **Códigos de Usuario**: Sistema de identificación único (`user_code`) para cada trabajador
- **Componentes Livewire**: Migración progresiva de Blade a componentes reactivos
- **Autenticación**: Login con Laravel Jetstream

### Julio - Septiembre 2022: Construcción de Fundamentos
- **Gestión de Usuarios**: CRUD completo de trabajadores
- **Permisos por Rol**: Lógica inicial de autorización (Owner, Admin, Member)
- **Nombres Completos**: Soporte para apellidos (`family_name1`, `family_name2`)
- **Búsqueda y Filtrado**: Primeras versiones funcionales
- **Localización**: Formato de fechas español (d/m/Y H:i)
- **Sesión Segura**: Cierre automático por inactividad
- **Calendario FullCalendar**: Integración inicial con eventos arrastrables

### Octubre - Diciembre 2022: Refinamiento Inicial
- **Optimización de Eventos**: Mejoras en performance de consultas a `events` table
- **Estadísticas v1**: Primeros gráficos funcionales con horas trabajadas
- **Tipos de Evento**: Clasificación básica (Jornada, Vacaciones, Pausa)
- **Códigos de Color**: Sistema visual para diferenciar tipos
- **Mejoras CSS**: Refinamiento continuo de interfaz con Tailwind
- **Reportes Básicos**: Primera versión de exportación de datos
- **Validación de Horarios**: Lógica inicial para verificar solapamientos

---

## 🔧 Características Técnicas Actuales

### Stack Tecnológico
- **Backend**: Laravel 10.x + Jetstream + Livewire 3.x
- **Frontend**: Tailwind CSS 3.x + Alpine.js + FullCalendar 6.x
- **Base de Datos**: MySQL 8.0+ con soporte completo de timezone
- **API**: RESTful con Sanctum para autenticación móvil
- **PDF**: Browsershot (Puppeteer/Chromium) + mPDF como fallback
- **Móvil**: Flutter con WebView híbrida y sincronización NFC

### Integraciones Externas
- API de festivos españoles (calendario oficial)
- Sistema NFC para fichajes sin contacto
- Notificaciones push en tiempo real
- Servicios de geolocalización

### Seguridad Implementada
- Autenticación 2FA opcional (Fortify)
- Tokens de sesión seguros (Sanctum)
- Sanitización HTML automática (HTMLPurifier)
- Auditoría completa de acciones críticas
- Rate limiting en endpoints API
- Protección CSRF en todos los formularios
- Hash seguro de contraseñas (bcrypt)

---

## 📊 Estadísticas del Proyecto

- **Período de Desarrollo**: Mayo 2022 - Enero 2026 (44 meses)
- **Commits Totales**: 695+
- **Idiomas Soportados**: Español, Inglés
- **Permisos del Sistema**: 60+
- **Roles Predefinidos**: 4 (Administrador, Editor, Usuario, Inspector)
- **Endpoints API**: 15+
- **Modelos Eloquent**: 20+
- **Componentes Livewire**: 25+
- **Documentos**: 10+ manuales (multiidioma)

---

## 🎯 Roadmap Futuro

### Próximas Funcionalidades
- **Reportes Avanzados**: Informes personalizables por usuario con filtros dinámicos
- **Dashboard Configurable**: Widgets arrastrables y preferencias guardadas
- **PWA Mejorada**: Aplicación móvil nativa con mejor offline support
- **Notificaciones Push**: Sistema mejorado de alertas en tiempo real
- **Integración con Calendarios**: Sincronización bidireccional con Google Calendar y Outlook
- **API Pública**: Endpoints documentados para integraciones de terceros
- **Turnos Rotativos**: Gestión automática de horarios por turnos
- **Gestión de Ausencias**: Módulo completo para bajas médicas y permisos
- **Nóminas Básicas**: Cálculo automático de horas para nóminas

### Mejoras Técnicas Planificadas
- **Laravel 11**: Migración a próxima versión LTS
- **Redis Cache**: Implementación de caché distribuida para mejor rendimiento
- **Queue Workers**: Procesamiento asíncrono de tareas pesadas (reportes, emails)
- **Websockets**: Actualizaciones en tiempo real sin polling
- **Tests Automatizados**: Incremento de cobertura con PHPUnit y Pest
- **CI/CD Pipeline**: Automatización completa de despliegues

---

**Última actualización**: 3 de Enero de 2026  
**Versión actual**: 1.0.0  
**Desarrollado por**: pbenav  
**Licencia**: Propietaria

---

## 💖 Apoya el Proyecto

👉 **[Apoyar en Patreon](https://www.patreon.com/cw/CTH_ControlHorario)**
