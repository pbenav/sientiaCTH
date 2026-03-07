# Manual de Usuario - CTH (Control de Tiempo y Horarios)

Bienvenido al manual de usuario de CTH, tu sistema integral de control de tiempo y horarios. Este manual te guiará paso a paso para aprovechar al máximo todas las funcionalidades de la aplicación.

## 📋 Tabla de Contenidos

1. [Introducción y Acceso](#1-introducción-y-acceso)
2. [Dashboard (Panel Principal)](#2-dashboard-panel-principal)
3. [Sistema de Fichajes y SmartClockIn](#3-sistema-de-fichajes-y-smartclockin)
4. [Gestión de Eventos](#4-gestión-de-eventos)
5. [Control de Duración de Jornada](#5-control-de-duración-de-jornada)
6. [Calendario y Horarios](#6-calendario-y-horarios)
7. [Informes y Estadísticas](#7-informes-y-estadísticas)
8. [Configuración Personal](#8-configuración-personal)
9. [Funciones de Equipo](#9-funciones-de-equipo)
10. [Preguntas Frecuentes](#10-preguntas-frecuentes)
11. [Solución de Problemas](#11-solución-de-problemas)

---

## 1. Introducción y Acceso

### ¿Qué es CTH?

CTH (Control de Tiempo y Horarios) es una aplicación web diseñada para gestionar el tiempo de trabajo, fichajes, horarios y estadísticas de productividad de forma intuitiva y eficiente.

### Características Principales

- ✅ **SmartClockIn**: Sistema inteligente de fichaje automático de entrada y salida.
- ✅ **Gestión de Eventos**: Creación y edición de registros de tiempo (fichajes, pausas, etc.).
- ✅ **Calendario Integrado**: Vista completa de horarios y eventos.
- ✅ **Informes Detallados**: Estadísticas y métricas de productividad.
- ✅ **Gestión de Equipos**: Colaboración y supervisión de equipos de trabajo.
- ✅ **Días Festivos**: Importación automática de calendarios laborales.

### Acceso al Sistema

#### Primer Acceso
1. **Abre tu navegador web** y dirígete a la URL proporcionada por tu administrador.
2. **Haz clic en "Registro"** si es tu primera vez.
3. **Completa el formulario** con tus datos:
   - Nombre completo
   - Primer apellido
   - Segundo apellido
   - Correo electrónico
   - Contraseña segura
   - Código de usuario (proporcionado por tu administrador)

![Pantalla de Registro](images/registro-usuario.png)
*Caption: Interfaz de registro para nuevos usuarios en el sistema CTH.*

#### Acceso Regular
1. **Introduce tu correo electrónico**.
2. **Introduce tu contraseña**.
3. **Marca "Recuérdame"** si deseas mantener la sesión activa.
4. **Haz clic en "Acceder"**.

![Pantalla de Login](images/login-usuario.png)
*Caption: Formulario de acceso (Login) al sistema.*

#### Recuperación de Contraseña
Si olvidaste tu contraseña:
1. **Haz clic en "¿Olvidaste tu contraseña?"**.
2. **Introduce tu correo electrónico**.
3. **Revisa tu email** para el enlace de recuperación.
4. **Sigue las instrucciones** del correo recibido.

---

## 2. Dashboard (Panel Principal)

### Vista General del Dashboard

El **Dashboard** es tu centro de control principal donde puedes ver toda la información relevante de un vistazo. En el mundo del software, este término se mantiene habitualmente en inglés para referirse al panel de control principal.

![Dashboard Principal](images/dashboard-principal.png)
*Caption: Vista general del Dashboard con métricas y accesos rápidos.*

### Elementos del Dashboard

#### A. Barra de Navegación Superior
- **Logo CTH**: Regresa al Dashboard desde cualquier página.
- **Menú Principal**: Acceso rápido a todas las secciones.
- **Notificaciones**: Alertas y mensajes importantes (icono de campana).
- **Perfil de Usuario**: Configuración personal y cambio de idioma (avatar).

#### B. Métricas Principales (Cards)
Las tarjetas o **Cards** superiores muestran información clave:

1. **Horas Trabajadas Hoy**: Tiempo total acumulado en el día actual.
2. **Eventos Pendientes**: Número de registros sin cerrar (p. ej., una jornada iniciada).
3. **Horas Semanales**: Total de horas trabajadas en la semana actual.
4. **Productividad**: Métrica de rendimiento basada en tus objetivos configurados.

![Métricas del Dashboard](images/metricas-dashboard.png)
*Caption: Detalle de las tarjetas de métricas (Cards) en el panel principal.*

#### C. Gráficos y Estadísticas
El Dashboard incluye visualizaciones como el **Pie chart** (gráfico de sectores) para la distribución del tiempo y gráficos de barras para la evolución semanal.

---

## 3. Sistema de Fichajes y SmartClockIn

### Tipos de Fichaje Disponibles

#### A. SmartClockIn (Fichaje Inteligente)
El **SmartClockIn** es el sistema avanzado de CTH que detecta automáticamente cuándo debes realizar un fichaje.

**¿Cómo funciona?**
1. **Detección**: El sistema analiza tu horario y patrones de uso.
2. **Sugerencias**: Te propone realizar el **Clock-in** (entrada) o **Clock-out** (salida) cuando corresponde.
3. **Confirmación**: Con un solo clic puedes validar la acción propuesta.

![SmartClockIn](images/smart-clockin.png)
*Caption: Interfaz del sistema SmartClockIn sugiriendo un fichaje.*

#### B. Fichaje Manual
Para situaciones donde prefieres el control total o el sistema automático no es aplicable.

**Pasos para fichaje manual:**
1. **Haz clic en "Nuevo Evento"** en el Dashboard.
2. **Selecciona el tipo de evento**: Entrada, Salida, Descanso, etc.
3. **Completa la información**: Fecha, hora y una breve descripción.
4. **Haz clic en "Guardar"**.

#### C. Fichaje Excepcional
Si intentas realizar un fichaje fuera del margen de tiempo permitido por tu horario, el sistema te permitirá realizar un **Fichaje Excepcional**. Recibirás un enlace por correo electrónico para validar esta acción si es necesario.

#### D. Sistema de Pausas (Pause/Resume)
CTH permite pausar tu jornada laboral de forma sencilla.

- **Pausar (Pause)**: Detiene el contador de tiempo productivo. Útil para citas médicas o gestiones personales.
- **Reanudar (Resume)**: Continúa la jornada desde donde se dejó.

![Sistema de Pausas](images/sistema-pausas.png)
*Caption: Botones de Pausa y Reanudación en la interfaz de SmartClockIn.*

---

## 4. Gestión de Eventos

### ¿Qué es un Evento?
En CTH, cada registro de tiempo (una entrada, una salida, una pausa) se denomina **Evento**.

### Historial de Eventos
Puedes consultar todos tus registros en la sección de Historial. Aquí verás detalles como la duración, el tipo de evento y las observaciones.

![Historial de Eventos](images/historial-eventos.png)
*Caption: Listado histórico de eventos registrados por el usuario.*

---

## 5. Control de Duración de Jornada

### Validación Automática
CTH monitoriza continuamente la duración total de tu jornada laboral diaria. El sistema:
1. **Calcula** el total de minutos trabajados en el día.
2. **Compara** con el límite máximo establecido para tu turno.
3. **Bloquea** extensiones de jornada que excedan el límite legal o configurado.

### Asistente de Ajuste
Si una acción (como mover un evento en el calendario) provoca que se exceda el tiempo máximo, aparecerá un **Asistente de Ajuste** que te permitirá:
- **Ajustar hora de inicio**: Retrasar la entrada para mantener la duración.
- **Ajustar hora de salida**: Adelantar la salida para cumplir con el límite.
- **Ajustar proporcionalmente**: Redistribuir el tiempo entre tramos horarios.

### Cálculo de Jornadas (Días Equivalentes)
El sistema calcula automáticamente cuántos días de trabajo representan tus horas acumuladas basándose en tu **Jornada Laboral** configurada. 
*Por ejemplo: Si has trabajado 16 horas y tu jornada estándar es de 8h, el sistema informará "2 días equivalentes", facilitando la lectura de totales mensuales.*

---

## 6. Calendario y Horarios

### Vista de Calendario
El calendario te permite visualizar de forma gráfica tu jornada laboral. Puedes cambiar entre vistas mensual, semanal y diaria.

![Vista de Calendario](images/calendario-mensual.png)
*Caption: Vista mensual del calendario con los eventos coloreados por tipo.*

### Configuración de Horarios
Tu administrador asigna un horario base, pero puedes consultar tus turnos y días festivos directamente desde esta sección. Los días festivos se importan automáticamente para facilitar la planificación.

---

## 7. Informes y Estadísticas

### Generación de Informes
Puedes exportar tus datos de fichaje en varios formatos:
- **PDF**: Ideal para impresión o envío oficial. Incluye saltos de página por trabajador, fecha de emisión y nombres en formato profesional.
- **Excel/CSV**: Para análisis detallado de datos.

#### Formato Profesional de Nombres
En todos los informes y listados, los trabajadores aparecen identificados bajo el estándar: `DNI - Apellidos, Nombre`. Este formato garantiza una ordenación alfabética perfecta y una identificación administrativa inequívoca.

#### Trazabilidad de Impresión
Cada página de los informes PDF generados contiene en la esquina superior derecha la **fecha y hora exacta de emisión**, asegurando que siempre sepas si estás consultando la versión más reciente de los datos.
*Caption: Herramienta para filtrar y exportar informes de tiempo.*

---

## 8. Configuración Personal

### Perfil y Preferencias
Desde tu perfil puedes:
- Cambiar tu foto de avatar.
- Actualizar tu contraseña.
- **Cambiar el idioma** de la aplicación (Español/Inglés).
- Configurar el **Token** de acceso para la aplicación móvil.

![Configuración de Perfil](images/configuracion-perfil.png)
*Caption: Panel de ajustes personales y seguridad.*

---

## 9. Funciones de Equipo

Si tienes el rol de **Administrador** o **Supervisor**, tendrás acceso a funciones adicionales:
- Ver el estado en tiempo real de los miembros del equipo.
- Aprobar fichajes excepcionales.
- Gestionar los centros de trabajo y etiquetas NFC para el fichaje móvil.

---

## 10. Preguntas Frecuentes

**¿Qué es un Token?**
Un **Token** es una clave alfanumérica única que permite a la aplicación móvil identificarte de forma segura sin necesidad de introducir tu contraseña constantemente.

**¿Por qué no puedo fichar?**
Asegúrate de estar dentro del rango horario permitido o solicita un enlace de fichaje excepcional a tu supervisor.

---

## 11. Solución de Problemas

Si experimentas problemas con el **Dashboard** o el sistema **SmartClockIn**, intenta recargar la página (F5) o limpiar la caché de tu navegador. Para problemas persistentes, contacta con el soporte técnico proporcionando tu ID de usuario.

---
*Manual actualizado: Febrero 2026*
*Versión: 1.0.1*
---

## 💖 Apoya el Proyecto

Si este proyecto te resulta útil y te gustaría apoyar su mantenimiento y desarrollo continuo, considera realizar una donación a través de Patreon:

👉 **[Apoyar en Patreon](https://www.patreon.com/cw/CTH_ControlHorario)**

Cualquier apoyo es enormemente agradecido y ayuda a que CTH siga siendo gratuito y de código abierto para todos.

---
*© 2025 CTH - Control de Tiempo y Horarios*
