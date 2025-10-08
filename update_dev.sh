#!/bin/bash

# Este script prepara y reinicia el entorno de desarrollo de Laravel.
# Es ideal para cuando se clona un repositorio o se necesita limpiar el proyecto.

echo "Preparando el entorno de desarrollo de Laravel..."

# 1. Instalar todas las dependencias
# Incluye las dependencias de desarrollo (como herramientas de testing).
echo "-> Instalando todas las dependencias de Composer..."
composer install

# 2. Limpiar las cachés de configuración
# Esto asegura que no haya datos de caché del entorno de producción.
echo "-> Limpiando cachés de la aplicación..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Borrar y regenerar la base de datos
# Útil para empezar de cero con la base de datos de desarrollo.
#echo "-> Reiniciando la base de datos y sembrando..."
#php artisan migrate:fresh --seed

# 4. Instalar las dependencias de npm
# Esto es necesario para que los comandos de compilación funcionen.
echo "-> Instalando dependencias de npm..."
npm install

# 5. Iniciar el servidor de desarrollo de assets
# El comando 'npm run dev' o 'npm run watch' es el clave aquí.
# Esto compila los assets y los observa para cambios automáticos.
echo "-> Compilando activos y activando el modo de observación..."
npm run dev

echo "¡Entorno de desarrollo listo! Ahora puedes empezar a trabajar."