#!/bin/bash

# Este script optimiza y prepara una aplicación Laravel para su entorno de producción,
# utilizando Laravel Mix. Debe ejecutarse después de subir los archivos al servidor.

echo "Iniciando la optimización de Laravel (con Laravel Mix)..."

# 1. Instalar dependencias de producción
echo "-> Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

# 2. Limpiar cachés antiguas
echo "-> Limpiando cachés de la aplicación..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Optimizar para producción
echo "-> Generando cachés optimizadas..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Compilar assets para producción con Laravel Mix
echo "-> Compilando activos para producción con Laravel Mix..."
npm run prod

# 5. Ejecutar migraciones
echo "-> Ejecutando migraciones de la base de datos..."
php artisan migrate --force

echo "¡Optimización completada! La aplicación está lista para producción."