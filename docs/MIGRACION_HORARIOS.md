# Migración de Formato de Días del Horario Laboral

## Descripción General

Este documento describe una migración importante de datos que afecta las configuraciones de horarios laborales en la aplicación CTH. Si estás instalando o actualizando CTH, debes conocer este cambio.

## El Problema

### Formato Anterior (Antes de la Migración)
Los horarios laborales usaban abreviaturas españolas de días:
- `L` - Lunes
- `M` - Martes
- `X` - Miércoles
- `J` - Jueves
- `V` - Viernes
- `S` - Sábado
- `D` - Domingo

### Formato Actual (Después de la Migración)
Los horarios laborales ahora usan números ISO 8601:
- `1` - Lunes (Monday)
- `2` - Martes (Tuesday)
- `3` - Miércoles (Wednesday)
- `4` - Jueves (Thursday)
- `5` - Viernes (Friday)
- `6` - Sábado (Saturday)
- `7` - Domingo (Sunday)

## ¿Por qué este Cambio?

1. **Compatibilidad Internacional**: ISO 8601 es un estándar reconocido globalmente
2. **Soporte Multi-idioma**: Los números son independientes del idioma
3. **Mantenimiento Más Fácil**: Más simple de parsear y validar
4. **Mejor Localización**: Soporta futuras traducciones a otros idiomas

## Impacto en Instalaciones Nuevas

✅ **No se requiere acción** - Las instalaciones nuevas usan automáticamente el nuevo formato.

## Impacto en Instalaciones Existentes

⚠️ **Se requiere migración** - Las instalaciones existentes con horarios configurados necesitan ejecutar un comando de migración.

### Pasos de Migración

1. **Respalda tu base de datos** antes de ejecutar cualquier migración:
   ```bash
   php artisan db:backup  # Si tienes backup configurado
   # O realiza un respaldo manual de tu base de datos
   ```

2. **Ejecuta el comando de migración**:
   ```bash
   php artisan schedule:migrate-to-iso
   ```

3. **Verifica la migración**:
   El comando mostrará un resumen indicando:
   - Total de horarios procesados
   - Horarios migrados exitosamente
   - Horarios ya en formato ISO (omitidos)
   - Cualquier error encontrado

### Detalles del Comando de Migración

El comando `schedule:migrate-to-iso`:
- ✅ Es **idempotente** - seguro de ejecutar múltiples veces
- ✅ Valida todos los datos antes de hacer cambios
- ✅ Preserva las configuraciones de horario existentes
- ✅ Detecta y maneja automáticamente formatos mixtos
- ✅ Proporciona progreso detallado y reporte de errores

### Qué se Migra

El comando actualiza la tabla `user_metas` donde:
- `meta_key` = `'work_schedule'`
- `meta_value` contiene el JSON del horario con arrays de días

### Ejemplo de Migración

**Antes:**
```json
[
  {
    "days": ["L", "M", "X", "J", "V"],
    "start": "09:00",
    "end": "17:00"
  }
]
```

**Después:**
```json
[
  {
    "days": [1, 2, 3, 4, 5],
    "start": "09:00",
    "end": "17:00"
  }
]
```

## Actualizaciones de Código Relacionadas

Esta migración también requirió actualizaciones en varios componentes de la aplicación:

### Archivos Modificados
- `app/Traits/Stats/CalculatesScheduledData.php` - Cálculos de estadísticas
- `app/Traits/Stats/CalculatesDashboardData.php` - Métricas del dashboard
- `app/Services/SmartClockInService.php` - Funcionalidad de fichaje

Estos archivos ahora trabajan exclusivamente con números ISO de días y han sido actualizados para asegurar cálculos correctos.

## Solución de Problemas

### Errores de Migración

Si el comando de migración reporta errores:

1. **Verifica la conectividad de la base de datos**:
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

2. **Verifica el formato JSON**:
   - Asegúrate de que los valores meta de `work_schedule` sean JSON válido
   - JSON corrupto será omitido con un mensaje de error

3. **Revisa los logs de error**:
   - Consulta los logs de Laravel para información detallada de errores
   - Ruta: `storage/logs/laravel.log`

### Las Estadísticas No se Calculan Correctamente

Si después de la migración las estadísticas muestran valores incorrectos:

1. **Verifica que la migración se completó**:
   ```bash
   php artisan schedule:migrate-to-iso
   ```
   Debería mostrar `0 migrados` y todos como "ya en formato ISO"

2. **Revisa los horarios en la base de datos**:
   ```sql
   SELECT user_id, meta_value 
   FROM user_metas 
   WHERE meta_key = 'work_schedule';
   ```
   Los arrays de `days` deberían contener números 1-7, no letras

3. **Limpia la caché de la aplicación**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

## Información de Versión

- **Migración introducida**: Versión 2.x (Noviembre 2024)
- **Comando de migración**: `schedule:migrate-to-iso`
- **Commits relacionados**: `f898c6e0` y `fed64cea`

## Soporte

Si encuentras problemas con esta migración:
1. Consulta esta documentación primero
2. Revisa la salida del comando de migración
3. Consulta los logs de Laravel para errores
4. Asegúrate de que todo el código esté actualizado del repositorio

## Ver También

- [Manual del Desarrollador](DEVELOPER_MANUAL.md) - Guía completa de desarrollo
- [Manual del Usuario](USER_MANUAL.md) - Documentación para usuarios
- Código fuente del comando de migración: `app/Console/Commands/MigrateWorkScheduleDaysToISO.php`
