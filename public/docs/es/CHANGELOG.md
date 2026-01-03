# 📜 Registro de Cambios - CTH

## Resumen Histórico

Este documento recoge los hitos más importantes en la evolución de **CTH (Control de Tiempo y Horarios)**, desde su creación hasta la actualidad.

---

## 🎯 Septiembre 2025 - Fundación y Sistema Base

### Características Iniciales
- **Sistema de Fichajes**: Implementación del sistema básico de entrada/salida
- **Eventos Todo el Día**: Soporte para eventos de jornada completa
- **Calendario Interactivo**: Integración de FullCalendar con localización en español
- **Gestión de Equipos**: Sistema multiequipo con roles básicos (Owner, Admin, Member, Inspector)
- **Tipos de Evento**: Sistema de clasificación de eventos con códigos de color
- **Estadísticas**: Panel de estadísticas en tiempo real

### Mejoras de UX/UI
- Modales reactivos para crear/editar eventos
- Sistema de validación de formularios
- Interfaz responsive para móviles
- Localización completa al español

---

## 📊 Octubre 2025 - Expansión y Seguridad

### Nuevas Funcionalidades
- **Centros de Trabajo**: Sistema completo de gestión de ubicaciones
  - Asociación con equipos
  - Dirección estructurada
  - Centro predeterminado por usuario
- **Gestión de Festivos**: Calendario de festivos por equipo con importación desde API externa
- **Sistema de Mensajería**: Comunicación interna entre usuarios y equipos
- **Notificaciones**: Sistema de alertas en tiempo real
- **Fichaje Excepcional**: Tokens de un solo uso para fichajes fuera de horario

### Seguridad y Autenticación
- **Login Avanzado**: Protección contra ataques de fuerza bruta
- **Períodos de Gracia**: Validación flexible de horarios
- **Cierre Automático**: Eventos abiertos se cierran automáticamente
- **Auditoría**: Sistema de registro de acciones críticas

### Mejoras del Sistema
- Actualización a Laravel 10
- Roles personalizados con permisos granulares (Editor añadido)
- Zonas horarias por equipo
- Cálculo inteligente de horas trabajadas vs. programadas
- Sistema de KPIs en el dashboard

---

## 🚀 Noviembre 2025 - SmartClockIn y API Móvil

### SmartClockIn
- **Fichaje Inteligente**: Botón que detecta automáticamente la siguiente acción
  - Inicio de jornada
  - Pausa/Reanudación
  - Fin de jornada
- **Validación de Horarios**: Integración con horarios laborales del usuario
- **Sistema de Pausas**: Gestión completa de descansos dentro de la jornada

### API Móvil
- **Endpoints RESTful**: API completa para aplicación Flutter
  - `/api/v1/clock` - Fichaje inteligente
  - `/api/v1/status` - Estado actual del usuario
  - `/api/v1/history` - Historial de fichajes
  - `/api/v1/schedule` - Horarios de trabajo
- **Autenticación por Código**: Login simplificado con `user_code`
- **Sistema NFC**: Soporte para fichaje mediante etiquetas NFC
- **Configuración Dinámica**: Descarga de configuración desde el servidor

### Mejoras de UX
- Internacionalización completa (ES/EN)
- Rediseño de vistas de eventos con Tailwind CSS
- Interfaz responsive mejorada
- Auto-scroll inteligente del calendario según horario laboral

---

## 📱 Diciembre 2025 - Reportes Profesionales y Dashboard

### Sistema de Reportes
- **Generación de PDF**: Integración con Browsershot para PDFs de alta calidad
  - Numeración de páginas automática
  - Diseño landscape optimizado
  - Metadatos completos (totales, promedios)
- **Exportación Múltiple**: Excel, CSV y PDF
- **Motor Alternativo**: mPDF como fallback para entornos sin Node.js
- **Localización**: Traducciones específicas para informes

### Dashboard Mejorado
- **Panel de Control**: Vista unificada con estadísticas clave
- **Resumen de Inbox**: Mensajes pendientes y recientes
- **Anuncios Acordeón**: Visualización compacta de avisos del equipo
- **Mensajes Difusión**: Función "Mensaje a todos" para comunicaciones masivas

### Sistema de Anuncios
- **Editor Enriquecido**: Soporte Markdown con vista previa
- **Publicación Programada**: Fechas de inicio/fin para anuncios
- **Permisos Granulares**: Control de quién puede crear/editar anuncios
- **Sanitización HTML**: Seguridad mejorada con HTMLPurifier

---

## 🎓 Enero 2026 - Documentación y Sistema de Permisos v1.0

### Documentación Integral
- **Visor Interno**: Sistema de documentación navegable dentro de la aplicación
- **Multiidioma**: Documentación completa en español e inglés
- **Manuales Consolidados**:
  - Manual de Usuario
  - Manual del Desarrollador
  - Referencia de API Móvil
  - Guía de Migración de Horarios
- **Enlaces Internos**: Navegación fluida entre documentos con anchors

### Sistema de Permisos Granular
- **PermissionMatrix**: Matriz centralizada de 60+ permisos
- **Roles del Sistema**:
  - **Administrador**: Control total del equipo
  - **Editor**: Usuario + gestión de anuncios
  - **Usuario**: Acceso estándar para fichajes
  - **Inspector**: Solo lectura para auditorías
- **Permisos Contextuales**: Validación por equipo y recurso
- **Migración Idempotente**: Sistema seguro para actualizar bases de datos existentes

### Optimización y Rendimiento
- **Consolidación de Migraciones**: Schema inicial unificado
- **Índices Estratégicos**: Optimización de consultas frecuentes
- **Caché de Permisos**: Reducción de consultas a base de datos
- **Lazy Loading**: Carga diferida de relaciones en modelos

### Mejoras del Sistema
- **Equipo de Bienvenida**: Team inicial para nuevos usuarios
- **Límites de Equipos**: Control de cuántos equipos puede crear cada miembro
- **Soporte Multiidioma**: Preferencia de idioma por usuario (ES/EN)
- **Filtrado de Documentación**: Contenido adaptado al idioma del usuario

---

## 🔧 Características Técnicas Actuales

### Stack Tecnológico
- **Backend**: Laravel 10 + Jetstream + Livewire
- **Frontend**: Tailwind CSS + Alpine.js + FullCalendar
- **Base de Datos**: MySQL 8.0+ con soporte timezone
- **API**: RESTful con autenticación Sanctum
- **PDF**: Browsershot (Puppeteer) + mPDF fallback
- **Móvil**: Flutter app con sincronización NFC

### Integraciones
- API de festivos españoles
- Sistema NFC para fichajes sin contacto
- WebView híbrida para app móvil
- Notificaciones push en tiempo real

### Seguridad
- Autenticación 2FA opcional
- Tokens de sesión seguros
- Sanitización HTML automática
- Auditoría completa de acciones
- Rate limiting en API

---

## 📊 Estadísticas del Proyecto

- **Commits Totales**: 695+
- **Idiomas Soportados**: Español, Inglés
- **Permisos del Sistema**: 60+
- **Roles Predefinidos**: 4
- **Endpoints API**: 15+
- **Documentos**: 10+ (multiidioma)

---

## 🎯 Próximos Pasos

- Mejoras en reportes avanzados
- Dashboard personalizable por usuario
- Aplicación móvil nativa mejorada
- Sistema de notificaciones push
- Integración con calendarios externos

---

**Última actualización**: Enero 2026  
**Versión actual**: 1.0.0  
**Desarrollado por**: pbenav
