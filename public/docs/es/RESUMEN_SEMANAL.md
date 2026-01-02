# 📅 Resumen de Mejoras y Nuevas Funcionalidades (Semana del 6-12 Diciembre)

Este documento resume el trabajo realizado en la última semana para estabilizar, corregir y mejorar la plataforma CTH.

---

## 🛠️ Backend y API

### 1. Refactorización de Rutas API (Clean API)
- **Cambio**: Se eliminó el prefijo `/mobile` de las rutas de la API, estandarizando el acceso a `/api/v1/...`.
- **Beneficio**: URLs más limpias y semánticas (ej. `/api/v1/clock` en lugar de `/mobile/clock`).
- **Compatibilidad**: Se mantuvieron alias para asegurar que versiones antiguas de la app sigan funcionando.

### 2. Relación de Eventos con Equipo
- **Dato**: Se añadieron campos `team_id` y `work_center_id` a la tabla `events`.
- **Mejora**: Ahora cada fichaje queda vinculado históricamente al equipo y centro donde se realizó, permitiendo que los usuarios cambien de equipo sin perder la trazabilidad de dónde ficharon anteriormente.
- **Migración**: Backfill masivo de datos históricos para asociar eventos pasados con sus equipos correctos.

### 3. Correcciones de Permisos y Servidor
- **PDF (mPDF)**: Se corrigió el error de permisos en producción que impedía generar PDFs. Ahora el directorio temporal apunta correctamente a `storage/app/mpdf`.
- **OpenBasedir**: Múltiples correcciones para la detección de ejecutables (Node, Chrome) en entornos con restricciones de seguridad (Plesk/Cpanel).

---

## 📱 Aplicación Móvil

### 1. Selector de Equipo (Team Switcher)
- **Funcionalidad**: Nueva capacidad en el perfil del usuario para cambiar entre distintos equipos y centros de trabajo.
- **Impacto**: Al cambiar de equipo en la app, se actualiza el `current_team_id` en el servidor, permitiendo fichar en diferentes empresas/sedes sin cerrar sesión.

### 2. Migración a API Limpia
- **Refactor**: Todos los servicios de la app (`Clock`, `Profile`, `History`, `Schedule`) fueron actualizados para usar los nuevos endpoints limpios.

### 3. Correcciones de Fichaje
- **Fix**: Solucionado el error "Invalid credentials" al intentar fichar en centros de trabajo de equipos secundarios.

---

## 📊 Reportes y Estadísticas

### 1. Agrupación Diaria en Listados
- **Mejora**: El listado de fichajes ahora permite agrupar eventos por día.
- **Detalle**: Se muestran subtotales diarios de Horas Trabajadas, Pausas y Horas Netas.

### 2. Totales en Exportación PDF
- **KPIs**: Se añadieron totales globales (Suma de horas, netas, pausas) al final de los reportes PDF generados (tanto en versión mPDF como Browsershot).

### 3. Corrección Reporte de Auditoría
- **Fix**: Se implementó un fallback robusto a mPDF cuando Browsershot falla (error "Node.js not found"), garantizando que el reporte de auditoría siempre se pueda descargar.

---

## 🎨 Frontend y UI

### 1. Nuevo Editor de Anuncios
- **Funcionalidad**: Reescriptura completa del gestor de anuncios.
- **Features**: 
    - Toggle entre modo Visual y Código Markdown/HTML.
    - Corrección de pegado (paste) desde otras fuentes.
    - Renderizado seguro de HTML y Markdown en el listado de anuncios.

### 2. Edición de Eventos
- **Permisos**: Refinada la lógica de `canModifyEvent` para permitir correctamente que administradores y propietarios de equipo editen eventos, incluso si también tienen rol de inspector.
- **UI**: Mejorada la visibilidad del botón de edición y modal en dispositivos móviles.

### 3. KPIs de Puntualidad
- **Refinamiento**: Ajuste en la lógica de cálculo y visualización del KPI "Adelanto Medio" para mostrar minutos enteros y mejorar su precisión ("Confianza").
