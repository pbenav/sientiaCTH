<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Role;

echo "=== Añadiendo permiso de impersonalización ===\n\n";

// Crear el nuevo permiso
$permission = Permission::firstOrCreate(
    ['name' => 'users.impersonate'],
    [
        'display_name' => 'Suplantar usuarios',
        'description' => 'Permite ver la aplicación como otro usuario',
        'category' => 'users',
        'requires_context' => false,
        'is_system' => true,
    ]
);

echo "✅ Permiso creado/encontrado: {$permission->id} - {$permission->display_name}\n\n";

// Asignar automáticamente a roles de Administrador
$adminRoles = Role::where('name', 'like', '%administrador%')->get();

echo "Asignando a roles de Administrador:\n";
foreach ($adminRoles as $role) {
    if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
        $role->permissions()->attach($permission->id);
        echo "  ✅ {$role->display_name} (Team: {$role->team->name})\n";
    } else {
        echo "  ⏭️  {$role->display_name} (ya tenía el permiso)\n";
    }
}

echo "\n📊 Total roles de Administrador actualizados: {$adminRoles->count()}\n";
echo "\n✨ Proceso completado\n";
