# 🛠️ Comandos de Consola Disponibles

Este documento describe todos los comandos de consola (Artisan) disponibles para gestión de eventos y mantenimiento del sistema.

## 📋 Comandos de Gestión de Eventos

### 1. `events:autoclose`
**Propósito**: Cierra automáticamente eventos no confirmados que han pasado su fecha de expiración

```bash
php artisan events:autoclose
```

**Funcionalidad**:
- ✅ Revisa todos los equipos con `event_expiration_days` configurado
- ✅ Cierra eventos abiertos que exceden el tiempo límite
- ✅ Registra en logs cada evento cerrado
- ✅ Actualiza `is_open = false` y `is_closed_automatically = true`

**Uso típico**: Ejecutar en cron job diario
```bash
# En crontab
0 2 * * * cd /path/to/project && php artisan events:autoclose
```

### 2. `events:fix-data`
**Propósito**: Analiza y corrige eventos con problemas de datos

```bash
# Ver problemas sin corregir
php artisan events:fix-data --dry-run

# Corregir problemas
php artisan events:fix-data

# Analizar usuario específico
php artisan events:fix-data --user=123 --dry-run

# Analizar rango de fechas
php artisan events:fix-data --from=2023-01-01 --to=2023-12-31
```

**Problemas que corrige**:
- ✅ Eventos sin fecha de fin (`end = null`)
- ✅ Eventos sin tipo (`event_type_id = null`)
- ✅ Eventos con `start > end`
- ✅ Eventos con duraciones anómalas

**Opciones**:
- `--dry-run`: Solo analizar, no aplicar cambios
- `--user=ID`: Analizar solo un usuario específico
- `--from=FECHA`: Fecha de inicio del análisis (Y-m-d)
- `--to=FECHA`: Fecha de fin del análisis (Y-m-d)

### 3. `events:update-extra-hours`
**Propósito**: Actualiza eventos existentes con nueva lógica de horas extra

```bash
# Ver qué cambiaría
php artisan events:update-extra-hours --dry-run

# Aplicar cambios
php artisan events:update-extra-hours
```

**Funcionalidad**:
- ✅ Aplica nueva lógica: solo eventos de "jornada laboral principal" NO son horas extra
- ✅ Actualiza campo `is_extra_hours` en todos los eventos
- ✅ Maneja eventos sin tipo de evento
- ✅ Reporte detallado de cambios

### 4. `events:verify-and-fix`
**Propósito**: Comando integral de verificación y corrección de inconsistencias

```bash
# Verificación completa (solo mostrar)
php artisan events:verify-and-fix --dry-run

# Corregir todo
php artisan events:verify-and-fix

# Corregir solo descripciones
php artisan events:verify-and-fix --fix-descriptions

# Corregir solo horas extra
php artisan events:verify-and-fix --fix-extra-hours

# Corregir solo tipos de jornada laboral
php artisan events:verify-and-fix --fix-workday-types
```

**Verificaciones incluidas**:
- ✅ Tipos de evento "Jornada laboral" marcados correctamente
- ✅ Eventos con descripciones apropiadas
- ✅ Lógica de horas extra consistente
- ✅ Eventos huérfanos (sin tipo)
- ✅ Integridad general de datos

**Opciones**:
- `--dry-run`: Solo mostrar lo que se corregiría
- `--fix-descriptions`: Solo corregir descripciones faltantes
- `--fix-extra-hours`: Solo corregir lógica de horas extra
- `--fix-workday-types`: Solo corregir tipos de jornada laboral

## 📊 Comandos de Mantenimiento

### Base de Datos
```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar migraciones paso a paso
php artisan migrate --step

# Revertir última migración
php artisan migrate:rollback

# Ver estado de migraciones
php artisan migrate:status
```

### Cache y Optimización
```bash
# Limpiar caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔄 Flujos de Trabajo Recomendados

### Mantenimiento Diario (Cron Jobs)
```bash
# 2:00 AM - Cerrar eventos automáticamente
0 2 * * * cd /path/to/project && php artisan events:autoclose

# 3:00 AM - Verificar y corregir datos
0 3 * * 0 cd /path/to/project && php artisan events:verify-and-fix
```

### Después de Actualizaciones
```bash
# 1. Ejecutar migraciones
php artisan migrate

# 2. Verificar estado
php artisan events:verify-and-fix --dry-run

# 3. Corregir si es necesario
php artisan events:verify-and-fix

# 4. Optimizar caches
php artisan config:cache
php artisan route:cache
```

### Resolución de Problemas
```bash
# 1. Identificar problemas
php artisan events:fix-data --dry-run

# 2. Verificar consistencia
php artisan events:verify-and-fix --dry-run

# 3. Corregir problemas específicos
php artisan events:fix-data --user=123
php artisan events:verify-and-fix --fix-descriptions

# 4. Verificación final
php artisan events:verify-and-fix --dry-run
```

### Migración de Datos (Nueva Lógica)
```bash
# 1. Actualizar tipos de evento
php artisan events:verify-and-fix --fix-workday-types

# 2. Actualizar lógica de horas extra
php artisan events:update-extra-hours

# 3. Corregir descripciones
php artisan events:verify-and-fix --fix-descriptions

# 4. Verificación final
php artisan events:verify-and-fix --dry-run
```

## 📝 Logging y Monitoreo

Todos los comandos registran su actividad en:
- **Archivo**: `storage/logs/laravel.log`
- **Nivel**: INFO para operaciones normales, ERROR para problemas

### Ejemplos de logs:
```
[2025-11-06 16:30:00] local.INFO: Starting AutoCloseEvents command...
[2025-11-06 16:30:01] local.INFO: Closing event 12345 for user 67 in team 1.
[2025-11-06 16:30:05] local.INFO: AutoCloseEvents completed. Closed 15 events.

[2025-11-06 16:35:00] local.INFO: Migration: Updated 185 events with new extra hours logic
[2025-11-06 16:35:00] local.INFO: Verification: Found 99 events without event type
```

## ⚠️ Consideraciones de Seguridad

### Antes de Ejecutar en Producción:
1. **Backup**: Siempre hacer backup de la base de datos
2. **Dry Run**: Usar `--dry-run` para ver el impacto
3. **Horario**: Ejecutar durante horas de bajo tráfico
4. **Logs**: Monitorear logs durante y después de la ejecución

### Comandos Seguros (Solo Lectura):
- `events:fix-data --dry-run`
- `events:verify-and-fix --dry-run`
- `events:update-extra-hours --dry-run`

### Comandos que Modifican Datos:
- `events:autoclose` ⚠️
- `events:fix-data` ⚠️
- `events:verify-and-fix` ⚠️
- `events:update-extra-hours` ⚠️

## 🆘 Solución de Problemas Comunes

### "No se encontraron eventos para procesar"
```bash
# Verificar filtros de fecha
php artisan events:fix-data --from=2023-01-01 --to=2025-12-31 --dry-run
```

### "Muchos eventos sin tipo"
```bash
# Corregir tipos de evento primero
php artisan events:verify-and-fix --fix-workday-types
```

### "Problemas de rendimiento con grandes volúmenes"
```bash
# Procesar por rangos de fecha
php artisan events:fix-data --from=2023-01-01 --to=2023-06-30
php artisan events:fix-data --from=2023-07-01 --to=2023-12-31
```

---

**✅ Todos los comandos están optimizados para ser seguros, eficientes y informativos.**