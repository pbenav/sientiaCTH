#!/bin/bash

# Este script optimiza y prepara una aplicación Laravel para su entorno de producción,
# utilizando Laravel Mix. Debe ejecutarse después de subir los archivos al servidor.

echo "🚀 Iniciando la optimización de Laravel (con Laravel Mix)..."

# 1. Instalar dependencias de producción
echo "📦 Instalando dependencias de Composer..."
npm update
npm cache clean --force
#composer install --no-dev --optimize-autoloader

# 2. Limpiar cachés antiguas
echo "🧹 Limpiando cachés de la aplicación..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Optimizar para producción
echo "⚡ Generando cachés optimizadas..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Compilar assets para producción con Laravel Mix
echo "🎨 Compilando activos para producción con Laravel Mix..."
npm run prod

# 5. Ejecutar migraciones
echo "🗄️  Ejecutando migraciones de la base de datos..."
php artisan migrate --force

# 6. Reiniciar servicios si es necesario
if command -v php-fpm &> /dev/null; then
    echo "🔄 Reiniciando PHP-FPM..."
    sudo systemctl restart php-fpm 2>/dev/null || sudo service php-fpm restart 2>/dev/null || echo "⚠️  No se pudo reiniciar PHP-FPM automáticamente"
fi

if [ -f artisan ]; then
    echo "🔄 Reiniciando workers de cola..."
    php artisan queue:restart 2>/dev/null || echo "ℹ️  No hay workers de cola corriendo"
fi

echo "✅ ¡Optimización completada! La aplicación está lista para producción."