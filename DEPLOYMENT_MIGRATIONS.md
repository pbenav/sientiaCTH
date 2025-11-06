# 🚀 Migraciones y Comandos para Producción

Este documento describe las migraciones y comandos creados para aplicar los cambios de funcionalidad de eventos y horas extra en producción.

## 📋 Migraciones Incluidas

### 1. `2025_11_06_162621_update_workday_types_for_all_teams.php`
**Propósito**: Actualiza todos los tipos de evento "Jornada laboral" para que tengan `is_workday_type = true`

**Lo que hace**:
- ✅ Verifica que las tablas y columnas existan
- ✅ Actualiza solo los tipos que necesitan cambio (evita operaciones innecesarias)
- ✅ Registra en logs cuántos tipos se actualizaron
- ✅ Manejo robusto de errores

**Rollback**: Revierte los cambios para equipos > 1 (preserva el estado original)

### 2. `2025_11_06_163036_update_existing_events_extra_hours_logic.php`
**Propósito**: Actualiza todos los eventos existentes con la nueva lógica de horas extra

**Lo que hace**:
- ✅ Usa SQL optimizado para manejar grandes volúmenes de datos
- ✅ Solo eventos del tipo "jornada laboral principal" NO son horas extra
- ✅ Eventos sin tipo se marcan como horas extra
- ✅ Registra cuántos eventos se actualizaron

**Rollback**: Conservador (no revierte perfectamente porque la lógica anterior era compleja)

### 3. `2025_11_06_163513_ensure_is_extra_hours_default_values.php`
**Propósito**: Asegura que la columna `is_extra_hours` tenga valores y restricciones correctas

**Lo que hace**:
- ✅ Crea la columna si no existe
- ✅ Asigna valores por defecto a registros con NULL
- ✅ Asegura restricción NOT NULL y valor por defecto

### 4. `2025_11_06_163626_fix_events_without_description.php`
**Propósito**: Corrige eventos sin descripción usando el nombre del tipo de evento

**Lo que hace**:
- ✅ Asigna descripción desde el tipo de evento
- ✅ Asigna descripción por defecto a eventos sin tipo
- ✅ Maneja casos NULL, vacío y 'null' (string)

## 🛠️ Comandos Disponibles

### 1. `php artisan events:update-extra-hours [--dry-run]`
Actualiza eventos existentes con nueva lógica de horas extra.

```bash
# Ver qué cambiaría sin aplicar cambios
php artisan events:update-extra-hours --dry-run

# Aplicar cambios
php artisan events:update-extra-hours
```

### 2. `php artisan events:verify-and-fix [opciones]`
Comando integral para verificar y corregir inconsistencias.

**Opciones**:
- `--dry-run`: Solo mostrar lo que se corregiría
- `--fix-descriptions`: Solo corregir descripciones
- `--fix-extra-hours`: Solo corregir lógica de horas extra
- `--fix-workday-types`: Solo corregir tipos de jornada laboral

```bash
# Verificación completa (modo dry-run)
php artisan events:verify-and-fix --dry-run

# Corregir todo
php artisan events:verify-and-fix

# Solo corregir descripciones
php artisan events:verify-and-fix --fix-descriptions

# Verificar un aspecto específico
php artisan events:verify-and-fix --fix-extra-hours --dry-run
```

## 🚀 Procedimiento de Despliegue en Producción

### Paso 1: Ejecutar Migraciones
```bash
# Ejecutar todas las migraciones nuevas
php artisan migrate

# O ejecutar paso a paso para control detallado
php artisan migrate --step
```

### Paso 2: Verificar Estado (Opcional)
```bash
# Verificar que todo esté correcto
php artisan events:verify-and-fix --dry-run
```

### Paso 3: Corrección Manual (Si es necesario)
```bash
# Si se detectan problemas, corregir
php artisan events:verify-and-fix
```

## 🔍 Verificaciones de Seguridad

Todas las migraciones incluyen:

✅ **Verificación de Existencia**: Tablas y columnas se verifican antes de usarse
✅ **Operaciones Idempotentes**: Se pueden ejecutar múltiples veces sin problemas
✅ **Manejo de Errores**: Try-catch completo con logging
✅ **Logging Detallado**: Registra qué se hizo y cuántos elementos se afectaron
✅ **Rollback Seguro**: Los rollbacks preservan la integridad de datos
✅ **Optimización**: Usa SQL directo para operaciones masivas

## 📊 Impacto Esperado

En un sistema típico:

- **Tipos de Evento**: ~70 tipos "Jornada laboral" se marcarán como principales
- **Eventos**: Cientos o miles de eventos se reclasificarán según nueva lógica
- **Descripciones**: Eventos sin descripción recibirán una apropiada
- **Rendimiento**: Las operaciones están optimizadas para manejar grandes volúmenes

## ⚠️ Consideraciones Importantes

1. **Base de Datos Grande**: Si tienes millones de eventos, considera ejecutar durante horarios de bajo tráfico
2. **Backups**: Siempre haz backup antes de ejecutar en producción
3. **Logs**: Revisa los logs después de ejecutar para confirmar que todo funcionó
4. **Rollback**: Los rollbacks están disponibles pero son conservadores para preservar integridad

## 🔄 Orden de Ejecución Recomendado

1. `update_workday_types_for_all_teams` - Corrige tipos de evento primero
2. `update_existing_events_extra_hours_logic` - Actualiza lógica de horas extra
3. `ensure_is_extra_hours_default_values` - Asegura integridad de columna
4. `fix_events_without_description` - Corrige descripciones faltantes

**Nota**: Laravel ejecuta las migraciones en orden cronológico automáticamente, por lo que el orden ya está garantizado.

## 📞 Soporte

Si encuentras problemas durante el despliegue:

1. Revisa los logs en `storage/logs/laravel.log`
2. Ejecuta `php artisan events:verify-and-fix --dry-run` para diagnosticar
3. Los comandos están diseñados para ser seguros y repetibles

---

**✅ Estas migraciones han sido probadas y están listas para producción.**