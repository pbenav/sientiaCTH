# Manual de Usuario - CTH (Control de Tiempo y Horarios)

Bienvenido al manual de usuario de CTH, tu sistema integral de control de tiempo y horarios. Este manual te guiará paso a paso para aprovechar al máximo todas las funcionalidades de la aplicación.

## 📋 Tabla de Contenidos

1. [Introducción y Acceso](#introducción-y-acceso)
2. [Panel Principal (Dashboard)](#panel-principal-dashboard)
3. [Sistema de Fichajes](#sistema-de-fichajes)
4. [Gestión de Eventos](#gestión-de-eventos)
5. [Calendario y Horarios](#calendario-y-horarios)
6. [Informes y Estadísticas](#informes-y-estadísticas)
7. [Configuración Personal](#configuración-personal)
8. [Funciones de Equipo](#funciones-de-equipo)
9. [Preguntas Frecuentes](#preguntas-frecuentes)
10. [Solución de Problemas](#solución-de-problemas)

---

## 1. Introducción y Acceso

### ¿Qué es CTH?

CTH (Control de Tiempo y Horarios) es una aplicación web diseñada para gestionar el tiempo de trabajo, fichajes, horarios y estadísticas de productividad de forma intuitiva y eficiente.

### Características Principales

- ✅ **Fichaje Inteligente**: Sistema automático de entrada y salida
- ✅ **Gestión de Eventos**: Creación y edición de eventos de tiempo
- ✅ **Calendario Integrado**: Vista completa de horarios y eventos
- ✅ **Informes Detallados**: Estadísticas y métricas de productividad
- ✅ **Gestión de Equipos**: Colaboración y supervisión de equipos
- ✅ **Días Festivos**: Importación automática de días festivos

### Acceso al Sistema

#### Primer Acceso
1. **Abre tu navegador web** y dirígete a la URL proporcionada por tu administrador
2. **Haz clic en "Registro"** si es tu primera vez
3. **Completa el formulario** con tus datos:
   - Nombre completo
   - Primer apellido
   - Segundo apellido
   - Correo electrónico
   - Contraseña segura
   - Código de usuario (proporcionado por tu administrador)

![Pantalla de Registro](images/registro-usuario.png)
*Pantalla de registro para nuevos usuarios*

#### Acceso Regular
1. **Introduce tu correo electrónico**
2. **Introduce tu contraseña**
3. **Marca "Recuérdame"** si deseas mantener la sesión activa
4. **Haz clic en "Acceder"**

![Pantalla de Login](images/login-usuario.png)
*Pantalla de acceso al sistema*

#### Recuperación de Contraseña
Si olvidaste tu contraseña:
1. **Haz clic en "¿Olvidaste tu contraseña?"**
2. **Introduce tu correo electrónico**
3. **Revisa tu email** para el enlace de recuperación
4. **Sigue las instrucciones** del correo recibido

---

## 2. Panel Principal (Dashboard)

### Vista General del Dashboard

El dashboard es tu centro de control principal donde puedes ver toda la información relevante de un vistazo.

![Dashboard Principal](images/dashboard-principal.png)
*Vista general del panel principal*

### Elementos del Dashboard

#### A. Barra de Navegación Superior
- **Logo CTH**: Regresa al dashboard desde cualquier página
- **Menú Principal**: Acceso rápido a todas las secciones
- **Notificaciones**: Alertas y mensajes importantes (campana)
- **Perfil de Usuario**: Configuración personal (avatar)

#### B. Métricas Principales (Cards)
Las tarjetas superiores muestran información clave:

1. **Horas Trabajadas Hoy**
   - Tiempo total trabajado en el día actual
   - Actualización en tiempo real
   - Código de color: Verde (completo), Amarillo (parcial), Rojo (insuficiente)

2. **Eventos Pendientes**
   - Número de eventos sin cerrar
   - Enlace directo para gestionarlos
   - Notificación visual si hay eventos antiguos

3. **Horas Semanales**
   - Total de horas trabajadas en la semana
   - Comparación con horas objetivo
   - Progreso visual en porcentaje

4. **Productividad**
   - Métrica de rendimiento basada en objetivos
   - Tendencia respecto a períodos anteriores
   - Indicadores de mejora

![Métricas del Dashboard](images/metricas-dashboard.png)
*Tarjetas con métricas principales*

#### C. Gráficos y Estadísticas

**Gráfico de Horas Trabajadas**
- Visualización de las últimas 4 semanas
- Comparación entre horas trabajadas y objetivos
- Identificación de patrones y tendencias

![Gráfico de Horas](images/grafico-horas.png)
*Gráfico de evolución de horas trabajadas*

**Distribución de Tiempo**
- Pie chart mostrando cómo se distribuye el tiempo
- Categorías: Trabajo regular, Horas extras, Descansos, etc.
- Ayuda a identificar áreas de mejora

#### D. Eventos Recientes
Lista de los últimos eventos registrados:
- **Fecha y hora** del evento
- **Tipo de evento** (Entrada, Salida, Descanso)
- **Duración** (si aplica)
- **Estado** (Abierto/Cerrado)
- **Acciones rápidas** (Editar/Ver detalles)

### Acciones Rápidas del Dashboard

#### Botón de Fichaje Rápido
El botón más prominente del dashboard permite fichar rápidamente:

```
┌─────────────────────────────┐
│     🕐 FICHAR AHORA        │
│                             │
│   [  ENTRADA / SALIDA  ]   │
│                             │
│  Último fichaje: 09:30     │
│  Estado: Trabajando        │
└─────────────────────────────┘
```

**Estados posibles:**
- **"Iniciar Jornada"**: Cuando no has fichado entrada
- **"Descanso"**: Durante el horario laboral
- **"Volver del Descanso"**: Cuando estás en pausa
- **"Finalizar Jornada"**: Al terminar el día

#### Widget de Tiempo Actual
Muestra información en tiempo real:
- Hora actual del sistema
- Tiempo transcurrido desde último fichaje
- Tiempo restante hasta fin de jornada
- Recordatorios automáticos

---

## 3. Sistema de Fichajes

### Tipos de Fichaje Disponibles

#### A. Fichaje Automático (Smart Clock-In)
El sistema más avanzado que detecta automáticamente cuándo debes fichar.

**¿Cómo funciona?**
1. **Detección Inteligente**: El sistema analiza tu ubicación, horario y patrones
2. **Sugerencias Automáticas**: Te propone fichar cuando corresponde
3. **Un Clic**: Solo necesitas confirmar la acción

![Smart Clock-In](images/smart-clockin.png)
*Interfaz del fichaje inteligente*

**Configuración del Smart Clock-In:**
1. Ve a **Configuración > Fichaje Inteligente**
2. **Activa la geolocalización** si trabajas desde ubicaciones específicas
3. **Configura tus horarios habituales**
4. **Establece recordatorios automáticos**

#### B. Fichaje Manual
Para situaciones especiales o cuando prefieres control total.

**Pasos para fichaje manual:**
1. **Haz clic en "Nuevo Evento"** en el dashboard
2. **Selecciona el tipo de evento:**
   - Entrada (Inicio de jornada)
   - Salida (Fin de jornada)
   - Inicio de Descanso
   - Fin de Descanso
   - Evento Personalizado

![Creación de Evento Manual](images/evento-manual.png)
*Formulario para crear evento manual*

3. **Completa la información:**
   - **Fecha y hora**: Se autocompleta con la actual
   - **Descripción**: Opcional, útil para referencias futuras
   - **Observaciones**: Notas adicionales si es necesario

4. **Haz clic en "Guardar Evento"**

#### C. Fichaje Excepcional
Para situaciones fuera del horario normal.

**¿Cuándo usar fichaje excepcional?**
- Trabajo fuera del horario establecido
- Fines de semana o días festivos
- Situaciones de emergencia
- Trabajo remoto no planificado

**Proceso de fichaje excepcional:**
1. **El sistema detecta** que estás fuera de horario
2. **Aparece una alerta** preguntando si deseas hacer fichaje excepcional
3. **Confirmas la acción** y proporcionas justificación
4. **Se crea el evento** marcado como excepcional

![Fichaje Excepcional](images/fichaje-excepcional.png)
*Diálogo de confirmación para fichaje excepcional*

#### D. Sistema de Pausas en la Jornada
**¡NUEVA FUNCIONALIDAD!** Sistema de pausas para interrumpir temporalmente la jornada laboral.

**¿Cuándo usar el sistema de pausas?**
- **Citas médicas** durante la jornada
- **Gestiones personales** fuera de la oficina
- **Descansos largos** no programados
- **Cambios de ubicación** de trabajo
- **Interrupciones** por emergencias familiares

**Flujo del sistema de pausas:**
```
🟢 TRABAJANDO → [Pausar Jornada] → 🟠 EN PAUSA → [Continuar Trabajo] → 🟢 TRABAJANDO
```

![Sistema de Pausas](images/sistema-pausas.png)
*Interfaz del sistema de pausas en SmartClockIn*

**Cómo usar las pausas:**

1. **Durante tu jornada laboral**, verás dos opciones:
   - **"Pausar Jornada"** (botón naranja)
   - **"Finalizar Jornada"** (botón rojo)

2. **Al pausar la jornada:**
   - Se crea automáticamente un evento de pausa
   - El tiempo de pausa NO cuenta como horas trabajadas
   - El sistema te mostrará el estado "En Pausa"

3. **Para continuar trabajando:**
   - Haz clic en **"Continuar Trabajo"** (botón azul)
   - Se cierra automáticamente el evento de pausa
   - Reanudas tu jornada laboral normal

**Ventajas del sistema de pausas:**
- ✅ **Flexibilidad** para gestionar interrupciones imprevistas
- ✅ **Precisión** en el registro de tiempo trabajado vs. no trabajado
- ✅ **Transparencia** con historial claro de pausas y reinicios
- ✅ **Cumplimiento** de horarios reales sin penalizaciones injustas

> **💡 Tip**: Las pausas aparecen en color naranja en tu calendario y histórico de eventos, diferenciándose claramente del tiempo de trabajo productivo.

### Estados de Fichaje

#### Indicadores Visuales
El sistema usa colores para mostrar tu estado actual:

- 🟢 **Verde**: Trabajando normalmente
- � **Naranja**: En pausa de jornada (NUEVO)
- �🟡 **Amarillo**: En descanso programado
- 🔴 **Rojo**: Fuera de horario laboral
- 🔵 **Azul**: Evento especial o excepcional

#### Paneles de Estado Actual

**Estado: Trabajando**
```
┌─────────────────────────────────────┐
│  Estado Actual: 🟢 TRABAJANDO      │
│                                     │
│  Entrada: 09:00                    │
│  Tiempo transcurrido: 3h 45m       │
│  En horario: Sí                    │
│                                     │
│  [ Pausar Jornada ] [ Fin Jornada ]│
└─────────────────────────────────────┘
```

**Estado: En Pausa**
```
┌─────────────────────────────────────┐
│  Estado Actual: 🟠 EN PAUSA        │
│                                     │
│  Jornada iniciada: 09:00           │
│  Pausado desde: 12:30              │
│  Tiempo en pausa: 15m              │
│                                     │
│      [ CONTINUAR TRABAJO ]          │
└─────────────────────────────────────┘
```

### Historial de Fichajes

#### Vista de Historial
Accede a **Eventos > Historial** para ver todos tus fichajes:

![Historial de Eventos](images/historial-eventos.png)
*Lista completa del historial de fichajes*

**Información mostrada:**
- **ID del Evento**: Identificador único
- **Fecha y Hora**: Cuándo ocurrió el fichaje
- **Tipo**: Entrada, Salida, Descanso, etc.
- **Duración**: Tiempo total del evento
- **Estado**: Abierto/Cerrado
- **Acciones**: Editar, Ver detalles, Eliminar

#### Filtros Disponibles
- **Por fechas**: Rango específico
- **Por tipo de evento**: Solo entradas, solo salidas, etc.
- **Por estado**: Eventos abiertos/cerrados
- **Por descripción**: Búsqueda de texto

### Corrección de Fichajes

#### ¿Cuándo corregir un fichaje?
- Olvidaste fichar a la hora correcta
- Error en la hora registrada
- Cambio en el tipo de evento
- Agregar descripción o observaciones

#### Proceso de Corrección
1. **Localiza el evento** en el historial
2. **Haz clic en "Editar"** (icono de lápiz)
3. **Modifica los campos necesarios**:

![Edición de Evento](images/editar-evento.png)
*Modal de edición de evento*

   - **Fecha/Hora de inicio**
   - **Fecha/Hora de fin**
   - **Descripción**
   - **Observaciones**
   - **Tipo de evento**

4. **Guarda los cambios**

> **⚠️ Importante**: Solo puedes editar eventos propios y dentro del período permitido por tu administrador.

---

## 4. Gestión de Eventos

### Creación de Eventos

#### Acceso a Creación de Eventos
- **Desde el Dashboard**: Botón "Nuevo Evento"
- **Desde el Calendario**: Clic en cualquier día/hora
- **Desde Eventos**: Botón "Crear Evento"

#### Formulario de Evento Completo

![Formulario Completo de Evento](images/formulario-evento.png)
*Formulario completo para crear un evento*

**Campos disponibles:**

1. **Tipo de Evento** (Requerido)
   - Entrada
   - Salida
   - Descanso
   - Reunión
   - Trabajo Remoto
   - Otros (personalizable)

2. **Fecha y Hora de Inicio** (Requerido)
   - Selector de fecha intuitivo
   - Selector de hora en formato 24h
   - Botón "Ahora" para usar tiempo actual

3. **Fecha y Hora de Fin**
   - Solo para eventos con duración
   - Se calcula automáticamente para algunos tipos
   - Validación para evitar solapamientos

4. **Descripción**
   - Campo de texto libre
   - Útil para referencias futuras
   - Se autocompletará con el nombre del tipo si se deja vacío

5. **Observaciones**
   - Campo de texto largo
   - Para notas adicionales
   - Información contextual

6. **Centro de Trabajo**
   - Selección automática basada en tu configuración
   - Cambio manual si trabajas desde múltiples ubicaciones

### Tipos de Eventos Especiales

#### Eventos Todo el Día
Para días festivos, vacaciones, o eventos que duran toda la jornada:

1. **Marca la casilla "Todo el día"**
2. **Solo especifica la fecha** (no hora)
3. **El sistema calculará** automáticamente la duración

#### Eventos Recurrentes
Para eventos que se repiten regularmente:

1. **Crea el evento inicial**
2. **Marca "Evento recurrente"**
3. **Especifica la frecuencia:**
   - Diaria
   - Semanal
   - Mensual
   - Personalizada

#### Eventos de Horas Extras
El sistema identifica automáticamente las horas extras:

- **Eventos fuera del horario normal** se marcan automáticamente
- **Solo eventos tipo "workday" NO son horas extras**
- **Puedes anular manualmente** la detección automática

### Gestión Avanzada de Eventos

#### Edición Masiva
Para modificar múltiples eventos a la vez:

1. **Ve a Eventos > Lista**
2. **Selecciona los eventos** (checkboxes)
3. **Usa "Acciones en lote"**:
   - Cambiar tipo
   - Actualizar descripción
   - Cerrar eventos
   - Exportar selección

![Edición Masiva](images/edicion-masiva.png)
*Interfaz para edición masiva de eventos*

#### Estados de Eventos

**Evento Abierto** 🟢
- Evento en curso
- Se puede modificar libremente
- Cuenta para tiempo actual

**Evento Cerrado** 🔴
- Evento finalizado
- Modificación limitada
- No afecta tiempo actual

**Evento Pendiente** 🟡
- Evento futuro programado
- Modificación libre
- Se activará automáticamente

### Validaciones y Restricciones

#### Validaciones Automáticas
- **No solapamiento**: No puedes tener dos eventos simultáneos
- **Orden temporal**: La hora de fin debe ser posterior al inicio
- **Límites de duración**: Eventos no pueden exceder 24 horas
- **Fechas futuras**: Limitación de eventos futuros según configuración

#### Restricciones por Rol
- **Usuario normal**: Solo sus propios eventos
- **Supervisor**: Eventos de su equipo
- **Administrador**: Todos los eventos

---

## 5. Calendario y Horarios

### Vista de Calendario

#### Acceso al Calendario
- **Menú principal > Calendario**
- **Dashboard > Widget de calendario**
- **Eventos > Vista calendario**

#### Vistas Disponibles

![Vista Mensual del Calendario](images/calendario-mensual.png)
*Vista mensual del calendario con eventos*

**Vista Mensual**
- Panorama completo del mes
- Eventos mostrados como puntos de colores
- Navegación rápida entre meses
- Resumen de horas por día

**Vista Semanal**
- Detalle de la semana completa
- Eventos con horarios específicos
- Ideal para planificación detallada
- Visualización de conflictos

![Vista Semanal del Calendario](images/calendario-semanal.png)
*Vista semanal con eventos detallados*

**Vista Diaria**
- Cronograma detallado del día
- Eventos con duración visual
- Espacios libres claramente visibles
- Planificación hora por hora

#### Leyenda de Colores
El calendario usa un código de colores intuitivo:

- 🟢 **Verde**: Trabajo regular
- 🔵 **Azul**: Descansos
- 🟡 **Amarillo**: Reuniones
- 🟠 **Naranja**: Trabajo remoto
- 🔴 **Rojo**: Horas extras
- ⚪ **Gris**: Días festivos

### Interacción con el Calendario

#### Crear Eventos desde Calendario
1. **Haz clic en cualquier día/hora**
2. **Se abre el modal de creación** con fecha preseleccionada
3. **Completa la información** del evento
4. **El evento aparece inmediatamente** en el calendario

#### Editar Eventos Existentes
1. **Haz clic en cualquier evento** del calendario
2. **Se abre el modal de información**
3. **Haz clic en "Editar"** para modificar
4. **Los cambios se reflejan** instantáneamente

#### Navegación Rápida
- **Flechas laterales**: Mes/semana anterior/siguiente
- **Botón "Hoy"**: Regresa al día actual
- **Selector de mes/año**: Navegación rápida a cualquier período

### Horarios de Trabajo

#### Configuración de Horarios

![Configuración de Horarios](images/configuracion-horarios.png)
*Panel de configuración de horarios de trabajo*

**Horario Estándar:**
- Lunes a Viernes: 9:00 - 18:00
- Descanso: 13:00 - 14:00
- Fines de semana: No laborables

**Horarios Flexibles:**
- Entrada flexible: 8:00 - 10:00
- Salida ajustada automáticamente
- Horas mínimas por día: 8 horas

**Turnos Rotativos:**
- Turno mañana: 6:00 - 14:00
- Turno tarde: 14:00 - 22:00
- Turno noche: 22:00 - 6:00

#### Gestión de Días Festivos

**Importación Automática:**
1. **Ve a Configuración > Días Festivos**
2. **Selecciona el año** que deseas importar
3. **Elige "Importar Todo"** o selecciona días específicos
4. **Los días se marcan automáticamente** en el calendario

![Importación de Días Festivos](images/importar-festivos.png)
*Modal de importación de días festivos*

**Días Festivos Personalizados:**
- Fechas específicas de tu empresa
- Días de cierre por vacaciones
- Eventos especiales de equipo

### Planificación y Recordatorios

#### Recordatorios Automáticos
El sistema puede enviarte recordatorios:

- **15 minutos antes** del inicio de jornada
- **5 minutos antes** del final de descanso
- **Al final** de la jornada laboral
- **Eventos programados** importantes

#### Planificación Semanal
Vista especial para planificar tu semana:

1. **Ve a Calendario > Planificación Semanal**
2. **Arrastra y suelta** eventos para reorganizar
3. **Establece objetivos** de horas por día
4. **El sistema calcula** automáticamente totales

---

## 6. Informes y Estadísticas

### Tipos de Informes Disponibles

#### A. Informe de Horas Trabajadas

![Informe de Horas](images/informe-horas.png)
*Informe detallado de horas trabajadas*

**Información incluida:**
- Horas totales del período
- Desglose por días
- Horas regulares vs. extras
- Comparación con objetivos
- Tendencias y promedios

**Filtros disponibles:**
- Rango de fechas personalizable
- Por tipo de evento
- Por centro de trabajo
- Incluir/excluir horas extras

#### B. Informe de Productividad

**Métricas incluidas:**
- Puntualidad promedio
- Cumplimiento de horarios
- Patrones de trabajo
- Eficiencia temporal
- Comparativas del equipo

#### C. Informe de Ausencias

**Información detallada:**
- Días no trabajados
- Razones de ausencia
- Patrones de ausencia
- Impacto en objetivos
- Recomendaciones

### Generación de Informes

#### Informe Básico
1. **Ve a Informes > Generar Informe**
2. **Selecciona el tipo** de informe
3. **Establece el período** (última semana, mes, trimestre, personalizado)
4. **Aplica filtros** si es necesario
5. **Haz clic en "Generar"**

![Generador de Informes](images/generador-informes.png)
*Interfaz para generar informes personalizados*

#### Informe Avanzado
Para análisis más profundos:

1. **Selecciona "Informe Avanzado"**
2. **Elige múltiples métricas:**
   - Horas trabajadas
   - Puntualidad
   - Productividad
   - Comparativas
3. **Configura gráficos** y visualizaciones
4. **Establece comparativas** con períodos anteriores

### Visualizaciones y Gráficos

#### Gráfico de Tendencias
Muestra la evolución de tus métricas a lo largo del tiempo:

![Gráfico de Tendencias](images/grafico-tendencias.png)
*Gráfico de evolución temporal de horas trabajadas*

**Análisis disponibles:**
- Tendencia general (ascendente/descendente)
- Patrones semanales
- Variaciones estacionales
- Puntos de mejora

#### Gráfico de Distribución
Pie chart que muestra cómo distribuyes tu tiempo:

- Trabajo productivo: 75%
- Descansos: 15%
- Reuniones: 8%
- Otros: 2%

#### Comparativas de Equipo
Si eres supervisor, puedes ver comparativas:

```
┌─────────────────────────────┐
│  Comparativa del Equipo     │
├─────────────────────────────┤
│  Juan:    ████████░░ 8.2h  │
│  María:   ██████████ 8.5h  │
│  Pedro:   ███████░░░ 7.8h  │
│  Ana:     █████████░ 8.1h  │
│                             │
│  Promedio equipo: 8.15h    │
│  Objetivo: 8.0h            │
└─────────────────────────────┘
```

### Exportación de Datos

#### Formatos Disponibles
- **PDF**: Para presentaciones e impresión
- **Excel**: Para análisis adicional
- **CSV**: Para importar en otras herramientas
- **JSON**: Para integraciones técnicas

#### Proceso de Exportación
1. **Genera el informe** que deseas exportar
2. **Haz clic en "Exportar"**
3. **Selecciona el formato**
4. **Configura opciones** adicionales:
   - Incluir gráficos
   - Nivel de detalle
   - Filtros aplicados
5. **Descarga el archivo**

### Alertas y Notificaciones

#### Alertas Automáticas
El sistema puede alertarte sobre:

- **Horas insuficientes** en la semana
- **Patrones irregulares** de trabajo
- **Objetivos no cumplidos**
- **Mejoras detectadas**

#### Configuración de Alertas
1. **Ve a Configuración > Notificaciones**
2. **Activa las alertas** que deseas recibir
3. **Configura umbrales** personalizados
4. **Elige el método** de notificación (email, dashboard, ambos)

---

## 7. Configuración Personal

### Acceso a Configuración
- **Clic en tu avatar** (esquina superior derecha)
- **Selecciona "Configuración"** del menú desplegable
- **O ve a Configuración** desde el menú principal

### Perfil Personal

#### Información Básica

![Configuración de Perfil](images/configuracion-perfil.png)
*Panel de configuración del perfil personal*

**Datos editables:**
- **Foto de perfil**: Sube una imagen (máx. 2MB)
- **Nombre completo**
- **Apellidos**
- **Correo electrónico**
- **Teléfono** (opcional)
- **Código de empleado**

#### Cambio de Contraseña
1. **Ve a la sección "Seguridad"**
2. **Introduce tu contraseña actual**
3. **Escribe la nueva contraseña** (mín. 8 caracteres)
4. **Confirma la nueva contraseña**
5. **Haz clic en "Actualizar Contraseña"**

**Requisitos de contraseña:**
- Mínimo 8 caracteres
- Al menos una mayúscula
- Al menos un número
- Al menos un carácter especial

### Preferencias de Trabajo

#### Horarios Personales
- **Hora de entrada preferida**
- **Hora de salida preferida**
- **Duración de descanso**
- **Días laborables** (si tienes horario especial)

#### Centro de Trabajo
- **Centro principal**: Tu ubicación habitual
- **Centros secundarios**: Otras ubicaciones donde trabajas
- **Trabajo remoto**: Configuración para teletrabajo

### Configuración de Notificaciones

#### Tipos de Notificación

![Configuración de Notificaciones](images/configuracion-notificaciones.png)
*Panel de configuración de notificaciones*

**Notificaciones de Fichaje:**
- ✅ Recordatorio de entrada
- ✅ Recordatorio de descanso
- ✅ Recordatorio de salida
- ✅ Fichajes pendientes

**Notificaciones de Informes:**
- ✅ Informe semanal automático
- ✅ Alertas de objetivos
- ✅ Comparativas de rendimiento

**Notificaciones del Sistema:**
- ✅ Actualizaciones importantes
- ✅ Mantenimiento programado
- ✅ Nuevas funciones

#### Canales de Notificación
- **Email**: Notificaciones por correo electrónico
- **Dashboard**: Alertas en la aplicación
- **Navegador**: Notificaciones push (si está habilitado)

### Configuración de Privacidad

#### Control de Datos
- **Compartir estadísticas** con el equipo
- **Mostrar estado** en tiempo real
- **Permitir comparativas** con compañeros
- **Datos en informes** de supervisores

#### Configuración de Sesión
- **Cerrar sesión automáticamente** después de inactividad
- **Recordar dispositivo** para futuros accesos
- **Requerir confirmación** para acciones críticas

### Idioma y Localización

#### Configuración Regional
- **Idioma de la interfaz**: Español, Inglés
- **Formato de fecha**: DD/MM/YYYY, MM/DD/YYYY
- **Formato de hora**: 24h, 12h (AM/PM)
- **Zona horaria**: Automática basada en ubicación

#### Personalización Visual
- **Tema**: Claro, Oscuro, Automático
- **Tamaño de fuente**: Pequeño, Normal, Grande
- **Densidad de información**: Compacta, Normal, Espaciosa

---

## 8. Funciones de Equipo

*Esta sección es relevante si eres supervisor, administrador o tienes permisos especiales de equipo.*

### Gestión de Equipos

#### Vista General del Equipo

![Dashboard de Equipo](images/dashboard-equipo.png)
*Panel de control para supervisores de equipo*

**Información disponible:**
- **Miembros activos** del equipo
- **Estado actual** de cada miembro
- **Estadísticas consolidadas** del equipo
- **Alertas y notificaciones** del grupo

#### Administración de Miembros

**Agregar Nuevos Miembros:**
1. **Ve a Equipo > Gestionar Miembros**
2. **Haz clic en "Invitar Miembro"**
3. **Introduce el email** del nuevo miembro
4. **Asigna rol** (Miembro, Supervisor)
5. **Envía la invitación**

**Gestión de Permisos:**
- **Miembro**: Puede ver solo sus datos
- **Supervisor**: Puede ver datos del equipo
- **Administrador**: Control total del equipo

### Supervisión de Fichajes

#### Panel de Supervisión

![Panel de Supervisión](images/panel-supervision.png)
*Vista de supervisión de fichajes del equipo*

**Información en tiempo real:**
- **Estado actual** de cada miembro
- **Horas trabajadas** en el día
- **Eventos pendientes** o problemáticos
- **Alertas** de incumplimientos

#### Aprobación de Eventos
Como supervisor, puedes aprobar eventos especiales:

1. **Eventos excepcionales** fuera de horario
2. **Correcciones** de fichajes
3. **Permisos** y ausencias
4. **Horas extras** no programadas

### Informes de Equipo

#### Informes Consolidados
- **Productividad del equipo** completo
- **Comparativas individuales**
- **Tendencias grupales**
- **Objetivos vs. resultados**

#### Análisis de Rendimiento
- **Identificación de patrones** problemáticos
- **Reconocimiento** de buen rendimiento
- **Sugerencias de mejora**
- **Planificación** de recursos

### Gestión de Horarios de Equipo

#### Planificación de Turnos
- **Asignación** de horarios individuales
- **Rotación** de turnos
- **Cobertura** de ausencias
- **Coordinación** de trabajo remoto

#### Días Festivos y Vacaciones
- **Calendario compartido** del equipo
- **Gestión de ausencias** coordinada
- **Planificación** de cobertura
- **Aprobación** de vacaciones

---

## 9. Preguntas Frecuentes

### Sobre Fichajes

**P: ¿Qué hago si olvidé fichar la entrada?**
R: Puedes crear un evento manual desde el dashboard o calendario. Ve a "Nuevo Evento", selecciona "Entrada", ajusta la hora correcta y agrega una observación explicando la situación.

**P: ¿Puedo fichar desde mi móvil?**
R: Sí, CTH es completamente responsive. Accede desde el navegador de tu móvil usando la misma URL y tendrás todas las funciones disponibles.

**P: ¿El sistema detecta mi ubicación?**
R: Solo si lo autorizas. El Smart Clock-In puede usar geolocalización para sugerir fichajes automáticos cuando llegues o salgas del trabajo.

**P: ¿Qué pasa si trabajo fuera de horario?**
R: El sistema detectará que estás fuera del horario normal y te preguntará si deseas hacer un "fichaje excepcional". Estos eventos se marcan especialmente para tu supervisor.

### Sobre Eventos y Calendario

**P: ¿Puedo programar eventos futuros?**
R: Sí, puedes crear eventos para fechas futuras. Esto es útil para planificar reuniones, trabajo remoto o citas programadas.

**P: ¿Cómo corrijo un error en un evento?**
R: Ve al historial de eventos, encuentra el evento que necesitas corregir y haz clic en "Editar". Puedes modificar fecha, hora, tipo y descripción.

**P: ¿Los eventos se sincronizan con mi calendario personal?**
R: Actualmente no hay sincronización automática, pero puedes exportar tus eventos en formato iCal para importarlos en tu calendario personal.

### Sobre Informes

**P: ¿Con qué frecuencia se actualizan mis estadísticas?**
R: Las estadísticas del dashboard se actualizan en tiempo real. Los informes detallados se procesan cada hora para incluir los últimos cambios.

**P: ¿Puedo compartir mis informes?**
R: Puedes exportar tus informes en PDF o Excel y compartirlos manualmente. Los supervisores pueden acceder a informes consolidados del equipo.

**P: ¿Por qué mis horas extras no aparecen correctamente?**
R: El sistema calcula automáticamente las horas extras basándose en tu horario configurado. Si hay discrepancias, verifica tu configuración de horarios o contacta a tu supervisor.

### Sobre Configuración

**P: ¿Puedo cambiar mi horario de trabajo?**
R: Los cambios de horario generalmente requieren aprobación de tu supervisor. Puedes solicitar el cambio desde Configuración > Horarios.

**P: ¿Cómo desactivo las notificaciones por email?**
R: Ve a Configuración > Notificaciones y desmarca "Notificaciones por email" o personaliza qué tipos de notificaciones deseas recibir.

**P: ¿Mis datos están seguros?**
R: Sí, CTH implementa las mejores prácticas de seguridad. Tus datos están encriptados y solo son accesibles por ti y, cuando corresponde, por tu supervisor directo.

---

## 10. Solución de Problemas

### Problemas Comunes

#### No Puedo Acceder al Sistema

**Síntomas:**
- Página no carga
- Error de conexión
- Mensaje "Sitio no disponible"

**Soluciones:**
1. **Verifica tu conexión a internet**
2. **Intenta desde otro navegador** (Chrome, Firefox, Safari)
3. **Borra la caché** del navegador (Ctrl+F5)
4. **Verifica la URL** con tu administrador
5. **Contacta soporte técnico** si el problema persiste

#### Problemas de Fichaje

**Síntoma: "No puedo crear eventos"**

**Posibles causas y soluciones:**
- **Horario restringido**: Verifica si tienes permisos para fichar en ese horario
- **Evento duplicado**: Comprueba que no tengas otro evento activo
- **Permisos insuficientes**: Contacta a tu supervisor
- **Error de red**: Actualiza la página e intenta de nuevo

**Síntoma: "El Smart Clock-In no funciona"**

**Soluciones:**
1. **Activa la geolocalización** en tu navegador
2. **Verifica tu configuración** de horarios
3. **Usa fichaje manual** como alternativa
4. **Contacta soporte** para configuración avanzada

#### Problemas de Rendimiento

**Síntoma: "La aplicación va lenta"**

**Soluciones:**
1. **Cierra otras pestañas** del navegador
2. **Actualiza el navegador** a la versión más reciente
3. **Limpia la caché** del navegador
4. **Verifica tu conexión** a internet
5. **Intenta en horario** de menor uso

### Contacto con Soporte

#### Información para Proporcionar
Cuando contactes soporte, incluye:

- **URL** de la aplicación
- **Navegador y versión** que usas
- **Descripción detallada** del problema
- **Pasos** que realizaste antes del error
- **Mensaje de error** exacto (si aplica)
- **Capturas de pantalla** del problema

#### Canales de Soporte
- **Email**: soporte@cth-app.com
- **Teléfono**: +XX-XXX-XXX-XXXX
- **Horario**: Lunes a Viernes, 9:00-18:00
- **Urgencias**: Disponible 24/7 para problemas críticos

### Consejos de Optimización

#### Para Mejor Rendimiento
- **Usa navegadores modernos** (Chrome 90+, Firefox 88+, Safari 14+)
- **Mantén actualizadas** las extensiones del navegador
- **Evita múltiples pestañas** de CTH abiertas
- **Cierra sesión** al finalizar el día

#### Para Mejor Experiencia
- **Personaliza tus notificaciones** según tus necesidades
- **Usa atajos de teclado** cuando estén disponibles
- **Configura recordatorios** para fichajes importantes
- **Revisa regularmente** tus estadísticas

---

## 📞 Soporte y Contacto

### Recursos Adicionales

- **Documentación técnica**: Disponible en el menú Ayuda
- **Videos tutoriales**: Próximamente disponibles
- **Comunidad de usuarios**: Forum interno de la empresa
- **Actualizaciones**: Notificaciones automáticas de nuevas funciones

### Feedback y Sugerencias

¡Tu opinión es importante para nosotros! Puedes enviar sugerencias a través de:
- **Formulario de feedback** en la aplicación
- **Email directo**: feedback@cth-app.com
- **Reuniones de mejora** trimestrales con usuarios

---

*Manual actualizado: 6 de noviembre de 2025*
*Versión del sistema: CTH 2025.11*
*© 2025 CTH - Control de Tiempo y Horarios*