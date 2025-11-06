# Sistema de Pausas en SmartClockIn - Documentación Técnica

## 🎯 Resumen de la Nueva Funcionalidad

Se ha implementado un sistema completo de pausas para la jornada laboral que permite a los usuarios interrumpir temporalmente su trabajo y luego reanudarlo, manteniendo un registro preciso del tiempo trabajado vs. tiempo en pausa.

## 🚀 Características Implementadas

### 1. Nuevo Tipo de Evento "Pausa"
- **Nombre**: "Pausa"
- **Color**: Naranja (#FFA500)
- **Tipo**: `is_break_type = true`
- **No cuenta como tiempo de trabajo**: `is_workday_type = false`
- **Sin límite de duración**: `max_duration_minutes = null`

### 2. Estados del Sistema Ampliados
- **🟢 TRABAJANDO**: Usuario en jornada laboral activa
- **🟠 EN PAUSA**: Usuario ha pausado temporalmente la jornada
- **🔵 REANUDANDO**: Transición de pausa a trabajo activo

### 3. Flujo de Usuario
```
Inicio Jornada → 🟢 TRABAJANDO → [Pausar] → 🟠 EN PAUSA → [Continuar] → 🟢 TRABAJANDO → Fin Jornada
```

## 🛠️ Implementación Técnica

### Archivos Modificados

#### Backend
1. **SmartClockInService.php**
   - Nuevo método: `pauseWorkday(User $user, int $pauseEventTypeId)`
   - Nuevo método: `resumeWorkday(User $user, int $pauseEventId)`
   - Lógica ampliada en `getClockAction()` para detectar pausas activas

2. **SmartClockButton.php** (Livewire)
   - Nuevo método: `pauseWorkday()`
   - Nuevo método: `clockOutFromWork()`
   - Manejo de acciones `working_options` y `resume_workday`

3. **Migración: 2025_11_06_174952_add_pause_event_type_to_teams.php**
   - Añade tipo de evento "Pausa" a todos los equipos existentes
   - Incluye rollback seguro

#### Frontend
4. **smart-clock-button.blade.php**
   - Nueva interfaz para estado "Trabajando" con opciones de pausa/finalizar
   - Nueva interfaz para estado "En Pausa" con opción de continuar
   - Indicadores visuales mejorados

5. **es.json** (Traducciones)
   - 12 nuevas cadenas de texto en español para la funcionalidad

#### Documentación
6. **Manuales de usuario** (ES/EN)
   - Nueva sección "Sistema de Pausas en la Jornada"
   - Actualización de indicadores visuales
   - Ejemplos de uso y ventajas

## 🎨 Interfaz de Usuario

### Estado: Trabajando
```
┌─────────────────────────────────────┐
│  🟢 TRABAJANDO                     │
│  Entrada: 09:00 | Transcurrido: 3h │
│                                     │
│  [Pausar Jornada] [Finalizar]      │
└─────────────────────────────────────┘
```

### Estado: En Pausa
```
┌─────────────────────────────────────┐
│  🟠 EN PAUSA                       │
│  Pausado desde: 12:30 (15 minutos) │
│                                     │
│        [CONTINUAR TRABAJO]          │
└─────────────────────────────────────┘
```

## 🔄 Casos de Uso

### 1. Cita Médica
- Usuario trabaja normalmente → Pausa para cita → Regresa y continúa
- Registro preciso: tiempo trabajado vs. tiempo de cita

### 2. Gestión Personal
- Pausa para trámites bancarios, gestiones oficiales, etc.
- Control transparente del tiempo no productivo

### 3. Cambio de Ubicación
- Pausa al salir de una oficina → Continúa en otra ubicación
- Flexibilidad para trabajo en múltiples localizaciones

## 📊 Ventajas del Sistema

### Para el Usuario
- **Flexibilidad**: Gestión autónoma de interrupciones
- **Transparencia**: Historial claro de todas las pausas
- **Precisión**: Registro exacto del tiempo trabajado

### Para el Supervisor
- **Visibilidad**: Conocimiento de patrones de pausa
- **Control**: Supervisión de tiempo productivo vs. pausas
- **Reportes**: Estadísticas detalladas de uso

### Para la Empresa
- **Cumplimiento**: Mejor control de horarios reales
- **Productividad**: Medición precisa del tiempo efectivo
- **Satisfacción**: Mayor flexibilidad laboral

## 🔍 Validaciones Implementadas

### Validaciones de Negocio
- Solo se puede pausar si hay una jornada activa
- Solo se puede reanudar si hay una pausa activa
- No se pueden tener múltiples pausas simultáneas
- El tipo de evento de pausa debe estar configurado

### Validaciones Técnicas
- Verificación de usuarios y equipos válidos
- Manejo de errores con mensajes claros
- Transacciones seguras en base de datos
- Logging de todas las operaciones

## 🧪 Testing Recomendado

### Casos de Prueba
1. **Flujo Normal**: Trabajar → Pausar → Continuar → Finalizar
2. **Múltiples Pausas**: Varias pausas en una misma jornada
3. **Pausas Largas**: Pausas de varias horas
4. **Errores**: Intentar pausar sin jornada activa
5. **Concurrencia**: Múltiples usuarios pausando simultáneamente

### Verificaciones
- [ ] Eventos se crean correctamente en BD
- [ ] Estados visuales cambian apropiadamente
- [ ] Cálculos de tiempo son precisos
- [ ] Traducciones funcionan correctamente
- [ ] Interfaces responsive en móvil

## 📈 Métricas de Impacto

### KPIs a Monitorear
- **Uso de pausas**: Frecuencia y duración promedio
- **Satisfacción**: Feedback de usuarios sobre flexibilidad
- **Precisión**: Mejora en registro de tiempo real
- **Productividad**: Tiempo efectivo vs. tiempo total

## 🚀 Despliegue

### Comandos de Despliegue
```bash
# 1. Ejecutar migración
php artisan migrate

# 2. Limpiar caché
php artisan config:clear
php artisan view:clear

# 3. Verificar tipos de evento
php artisan tinker
> EventType::where('name', 'Pausa')->count()
```

### Rollback si Necesario
```bash
php artisan migrate:rollback --step=1
```

## 📚 Documentación Relacionada

- **Manual de Usuario (ES)**: `doc/es/manual-usuario.md`
- **Manual de Usuario (EN)**: `doc/en/user-manual.md`
- **Código SmartClockInService**: `app/Services/SmartClockInService.php`
- **Componente Livewire**: `app/Http/Livewire/SmartClockButton.php`

---

**Implementado**: 6 de Noviembre, 2025  
**Versión**: CTH 2025.11  
**Estado**: ✅ Listo para Producción