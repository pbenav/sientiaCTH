#!/bin/bash

# Este script actualiza el entorno de desarrollo del proyecto.
# Asegúrate de ejecutarlo desde el directorio raíz del proyecto.

set -e

# Actualizar el repositorio
echo "📥 Actualizando el repositorio..."
git pull origin $(git rev-parse --abbrev-ref HEAD)

# Instalar dependencias de Composer
echo "📦 Instalando dependencias de PHP..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Instalar dependencias de Node.js
echo "📦 Instalando dependencias de Node.js..."
npm install

# Compilar los activos
echo "⚙️ Compilando activos..."
npm run dev

# Ejecutar migraciones de base de datos
echo "📂 Ejecutando migraciones..."
php artisan migrate --force

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generar cachés
echo "⚡ Generando cachés..."
php artisan config:cache
php artisan route:cache

# Verificar permisos de almacenamiento
echo "🔒 Verificando permisos..."
chmod -R 775 storage bootstrap/cache

# Confirmación final
echo "✅ Entorno de desarrollo actualizado correctamente."