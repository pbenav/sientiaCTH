# Prueba de Conversión de Equipos Personales a Compartidos

## Contexto
Cuando un administrador global transfiere la propiedad de un equipo personal a sí mismo, el sistema automáticamente:
1. Convierte el equipo personal en equipo compartido (`personal_team: false`)
2. Mueve al antiguo propietario al equipo de "Bienvenida" como miembro regular
3. Actualiza el `current_team_id` del antiguo propietario al equipo de Bienvenida
4. Permite al admin eliminar el equipo después de la transferencia

## Requisitos Previos
- Usuario administrador global (user ID 1: informatica@zafarraya.es)
- Equipo de Bienvenida debe existir (ID 75, creado por migración)
- Equipos personales disponibles para prueba (hay 68 en la base de datos actual)

## Pasos para Probar

### 1. Identificar un equipo personal para probar
```bash
# Listar equipos personales
php artisan tinker --execute="echo json_encode(Team::where('personal_team', true)->limit(5)->get(['id', 'name', 'user_id'])->toArray(), JSON_PRETTY_PRINT);"
```

Ejemplo de salida:
```json
[
    {
        "id": 2,
        "name": "Siente's Team",
        "user_id": 2
    }
]
```

### 2. Acceder al panel de administración
1. Iniciar sesión como admin global: `informatica@zafarraya.es`
2. Ir a la URL: `http://localhost:8000/admin/teams`
3. Buscar el equipo personal seleccionado (ejemplo: "Siente's Team")

### 3. Verificar estado actual del equipo
```bash
# Ver detalles del equipo antes de la transferencia
php artisan tinker --execute="
\$team = Team::find(2);
echo 'Team ID: ' . \$team->id . PHP_EOL;
echo 'Name: ' . \$team->name . PHP_EOL;
echo 'Owner ID: ' . \$team->user_id . PHP_EOL;
echo 'Is Personal: ' . (\$team->personal_team ? 'Yes' : 'No') . PHP_EOL;
"
```

### 4. Verificar estado del usuario antes de la transferencia
```bash
# Ver detalles del propietario actual
php artisan tinker --execute="
\$user = User::find(2);
echo 'User ID: ' . \$user->id . PHP_EOL;
echo 'Name: ' . \$user->name . PHP_EOL;
echo 'Current Team ID: ' . \$user->current_team_id . PHP_EOL;
echo 'Teams: ' . \$user->allTeams()->pluck('name')->implode(', ') . PHP_EOL;
"
```

### 5. Realizar la transferencia en la interfaz web
1. Hacer clic en "Editar" en el equipo seleccionado
2. En la sección "Propietario del Equipo", hacer clic en "Transferir Propiedad"
3. En el modal, seleccionar "Transferir a mí (Administrador Global)"
4. Hacer clic en "Transferir Propiedad"
5. **Verificar el mensaje de éxito**: Debe mostrar:
   > "Propiedad del equipo transferida correctamente. Equipo personal convertido a equipo compartido."

### 6. Verificar el equipo después de la transferencia
```bash
# Ver detalles del equipo después de la transferencia
php artisan tinker --execute="
\$team = Team::find(2);
echo 'Team ID: ' . \$team->id . PHP_EOL;
echo 'Name: ' . \$team->name . PHP_EOL;
echo 'Owner ID: ' . \$team->user_id . PHP_EOL;
echo 'Is Personal: ' . (\$team->personal_team ? 'Yes' : 'No') . PHP_EOL;
echo 'Expected: Owner ID = 1, Is Personal = No' . PHP_EOL;
"
```

**Resultado esperado:**
- `Owner ID: 1` (admin global)
- `Is Personal: No`

### 7. Verificar el antiguo propietario
```bash
# Ver detalles del antiguo propietario
php artisan tinker --execute="
\$user = User::find(2);
echo 'User ID: ' . \$user->id . PHP_EOL;
echo 'Name: ' . \$user->name . PHP_EOL;
echo 'Current Team ID: ' . \$user->current_team_id . PHP_EOL;
echo 'Current Team Name: ' . (\$user->currentTeam ? \$user->currentTeam->name : 'None') . PHP_EOL;
echo 'All Teams: ' . \$user->allTeams()->pluck('name')->implode(', ') . PHP_EOL;
"
```

**Resultado esperado:**
- `Current Team ID: 75` (Bienvenida)
- `Current Team Name: Bienvenida`
- El usuario debe estar en el equipo "Bienvenida"

### 8. Verificar que el equipo ahora se puede eliminar
1. En el panel de administración, recargar la página del equipo editado
2. Verificar que el botón "Eliminar Equipo" ahora está visible (antes decía "No se puede eliminar equipo personal")
3. Hacer clic en "Eliminar Equipo"
4. Confirmar la eliminación
5. Verificar que el equipo se eliminó correctamente

### 9. Verificar que los eventos históricos se preservaron
```bash
# Ver eventos del equipo eliminado (deben tener team_id = NULL)
php artisan tinker --execute="
\$events = DB::table('events')->whereNull('team_id')->limit(5)->get(['id', 'user_id', 'team_id', 'clock_in']);
echo json_encode(\$events->toArray(), JSON_PRETTY_PRINT);
"
```

**Resultado esperado:**
- Los eventos del equipo eliminado ahora tienen `team_id: null` (preservados para historial)

## Verificaciones Finales

### Estado del Equipo de Bienvenida
```bash
php artisan tinker --execute="
\$welcomeTeam = Team::find(75);
echo 'Welcome Team ID: ' . \$welcomeTeam->id . PHP_EOL;
echo 'Members Count: ' . \$welcomeTeam->users()->count() . PHP_EOL;
echo 'Members: ' . \$welcomeTeam->users()->pluck('name')->implode(', ') . PHP_EOL;
"
```

### Conteo de Equipos Personales
```bash
# Debe haber un equipo personal menos
php artisan tinker --execute="
echo 'Personal Teams Count: ' . Team::where('personal_team', true)->count() . PHP_EOL;
echo 'Expected: 67 (one less than before)' . PHP_EOL;
"
```

## Casos de Uso

### 1. Limpieza de Equipos Huérfanos
Cuando un empleado deja la empresa, su equipo personal queda huérfano. El admin puede:
1. Transferir el equipo a sí mismo → Se convierte en compartido
2. Revisar los miembros y eventos
3. Eliminar el equipo si ya no es necesario
4. El empleado se mueve automáticamente al equipo de Bienvenida

### 2. Consolidación de Equipos
Si múltiples equipos personales tienen contenido similar, el admin puede:
1. Transferir todos a sí mismo
2. Consolidar miembros en un solo equipo compartido
3. Eliminar los equipos duplicados
4. Los antiguos propietarios se mueven a Bienvenida

### 3. Migración de Personal a Compartido
Para migrar de la filosofía de "un equipo por usuario" a "equipos compartidos por departamento":
1. Transferir equipos personales al admin
2. Se convierten automáticamente en compartidos
3. Renombrar y configurar como equipos departamentales
4. Asignar miembros según corresponda

## Notas Importantes

- ✅ Solo funciona cuando el administrador global transfiere a sí mismo
- ✅ El equipo de Bienvenida debe existir (creado por migración)
- ✅ Los eventos históricos se preservan con `team_id = NULL`
- ✅ El antiguo propietario automáticamente se agrega al equipo de Bienvenida
- ✅ El `current_team_id` del antiguo propietario se actualiza a Bienvenida
- ✅ El equipo convertido ahora se puede eliminar sin restricciones

## Troubleshooting

### Error: "El nuevo propietario debe ser miembro del equipo"
**Solución:** Asegúrate de seleccionar "Transferir a mí (Administrador Global)" en el dropdown, no otro usuario.

### Error: "No se puede eliminar equipo personal"
**Solución:** Primero transfiere la propiedad al admin global. El equipo se convertirá automáticamente en compartido.

### El usuario antiguo propietario no aparece en Bienvenida
**Verificar:**
```bash
php artisan tinker --execute="
\$user = User::find(2); // Reemplazar 2 con el ID del usuario
echo 'Teams: ' . \$user->allTeams()->pluck('name')->implode(', ') . PHP_EOL;
"
```

### El equipo de Bienvenida no existe
**Ejecutar migración:**
```bash
php artisan migrate:refresh --path=/database/migrations/2025_12_01_140538_create_welcome_team.php
```

## Logs para Debugging

Si algo no funciona, revisar los logs de Laravel:
```bash
tail -f storage/logs/laravel.log
```

## Conclusión

Esta funcionalidad permite una gestión completa del ciclo de vida de equipos personales:
- **Creación:** Los nuevos usuarios se asignan al equipo de Bienvenida (no se crean equipos personales)
- **Migración:** Los equipos personales existentes se pueden convertir a compartidos
- **Eliminación:** Los equipos convertidos se pueden eliminar sin perder el historial
- **Preservación:** Los eventos históricos se mantienen con `team_id = NULL`
