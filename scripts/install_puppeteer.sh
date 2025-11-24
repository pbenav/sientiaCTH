#!/bin/bash

# Este script instala Puppeteer y configura Chromium en un servidor con restricciones.
# Asegúrate de ejecutarlo desde el directorio raíz del proyecto.

set -e

# Variables
CACHE_DIR="$HOME/.cache/puppeteer"
NODE_MODULES_DIR="$(pwd)/node_modules/puppeteer/.local-chromium"

# Crear directorio de caché si no existe
mkdir -p "$CACHE_DIR"

# Configurar la variable de entorno para la caché de Puppeteer
export PUPPETEER_CACHE_DIR="$CACHE_DIR"

# Instalar Puppeteer localmente
if [ ! -d "node_modules/puppeteer" ]; then
    echo "📦 Instalando Puppeteer..."
    npm install puppeteer
else
    echo "✅ Puppeteer ya está instalado."
fi

# Forzar la descarga de Chromium en la caché configurada
echo "⬇️ Descargando Chromium..."
npx puppeteer install

# Verificar la instalación de Chromium
CHROMIUM_PATH=$(find "$NODE_MODULES_DIR" -name "chrome" | head -n 1)
if [ -z "$CHROMIUM_PATH" ]; then
    echo "❌ No se pudo encontrar Chromium. Verifica los pasos anteriores."
    exit 1
fi

echo "✅ Chromium instalado en: $CHROMIUM_PATH"

# Crear un archivo de prueba para verificar Puppeteer
cat << 'EOF' > test_puppeteer.js
const puppeteer = require('puppeteer');
(async () => {
  const browser = await puppeteer.launch({
    executablePath: process.env.PUPPETEER_EXECUTABLE_PATH
  });
  const page = await browser.newPage();
  await page.goto('https://example.com');
  console.log(await page.title());
  await browser.close();
})();
EOF

# Configurar la variable de entorno para Browsershot
echo "export PUPPETEER_EXECUTABLE_PATH=$CHROMIUM_PATH" >> ~/.bashrc
export PUPPETEER_EXECUTABLE_PATH="$CHROMIUM_PATH"

# Ejecutar el archivo de prueba
node test_puppeteer.js

# Limpiar archivo de prueba
rm test_puppeteer.js

echo "✅ Puppeteer y Chromium configurados correctamente."