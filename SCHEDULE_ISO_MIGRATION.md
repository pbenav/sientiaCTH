# Migración de Horarios Laborales a Formato ISO

## Resumen
Se ha estandarizado el formato de almacenamiento de días de la semana en los horarios laborales, pasando de abreviaturas españolas (`L,M,X,J,V,S,D`) a números ISO 8601 (`1-7`, donde 1=Lunes, 7=Domingo).

## Cambios Realizados

### 1. Vista del Formulario de Horario
**Archivo**: `resources/views/livewire/profile/user-work-schedule-form.blade.php`

- Los checkboxes ahora guardan números ISO (1-7) en lugar de letras (L,M,X,J,V,S,D)
- La visualización sigue mostrando las letras españolas para el usuario
- Los nuevos horarios se guardarán automáticamente en formato ISO

### 2. Aplicación Móvil
**Archivo**: `cth_mobile/lib/screens/clock_screen.dart`

- Función `_normalizeDayToISO()` que convierte cualquier formato a número ISO
- Soporta:
  - Números ISO (1-7)
  - Abreviaturas españolas (L,M,X,J,V,S,D)
  - Nombres completos en inglés (monday, tuesday, etc.)
  - Nombres completos en español (lunes, martes, etc.)
- **Compatibilidad total** con datos antiguos y nuevos

### 3. Comando de Migración
**Archivo**: `app/Console/Commands/MigrateWorkScheduleDaysToISO.php`

Comando para migrar los horarios existentes en la base de datos.

## Instrucciones de Despliegue

### Paso 1: Subir cambios al servidor
```bash
# Subir los cambios al repositorio
git add .
git commit -m "Estandarización de formato de días en horarios laborales a ISO 8601"
git push
```

### Paso 2: En el servidor, actualizar el código
```bash
cd /ruta/al/proyecto/cth
git pull
```

### Paso 3: Ejecutar la migración de datos
```bash
php artisan schedule:migrate-to-iso
```

Este comando:
- Busca todos los horarios en `user_meta` con clave `work_schedule`
- Convierte las abreviaturas españolas (L,M,X,J,V,S,D) a números ISO (1-7)
- Mantiene los números ISO que ya existan
- Muestra un resumen de cuántos horarios se migraron

**Ejemplo de salida**:
```
Iniciando migración de horarios laborales...
Usuario 1: horario migrado correctamente
Usuario 2: ya usa formato ISO, saltando...
Usuario 3: horario migrado correctamente

=== Resumen de migración ===
Total de horarios procesados: 3
Horarios migrados: 2
Horarios saltados: 1
```

### Paso 4: Verificar en la app móvil
- La app móvil ya está preparada para manejar ambos formatos
- No requiere actualización inmediata
- Los usuarios verán el tramo horario correcto automáticamente

## Beneficios

✅ **Independencia del idioma**: Los números ISO son estándar internacional  
✅ **Compatibilidad**: El móvil sigue funcionando con datos antiguos  
✅ **Mantenibilidad**: Código más limpio y fácil de entender  
✅ **Escalabilidad**: Fácil añadir soporte para otros idiomas en el futuro  

## Rollback (si es necesario)

Si necesitas revertir los cambios:

1. Revertir el commit en Git
2. Los datos en la base de datos quedarán en formato ISO, pero seguirán funcionando
3. Si necesitas volver al formato de letras, puedes crear un comando inverso

## Notas Técnicas

- **Formato ISO 8601**: 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado, 7=Domingo
- **Compatibilidad**: La app móvil maneja automáticamente ambos formatos
- **Sin downtime**: Los cambios son retrocompatibles
