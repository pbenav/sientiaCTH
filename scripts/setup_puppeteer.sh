#!/bin/bash

# Este script instala y configura Puppeteer y Chromium en un servidor Linux.
# Asegúrate de ejecutarlo con permisos de superusuario.

set -e

# Actualizar paquetes e instalar dependencias necesarias para Chromium
sudo apt-get update
sudo apt-get install -y \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libxcomposite1 \
    libxrandr2 \
    libxdamage1 \
    libxkbcommon0 \
    libgbm1 \
    libpango-1.0-0 \
    libasound2 \
    chromium-browser

# Verificar la instalación de Chromium
if ! command -v chromium-browser &> /dev/null
then
    echo "❌ Chromium no se instaló correctamente. Verifica los pasos anteriores."
    exit 1
fi

CHROMIUM_PATH=$(which chromium-browser)
echo "✅ Chromium instalado en: $CHROMIUM_PATH"

# Configurar Puppeteer para usar el binario de Chromium
export PUPPETEER_EXECUTABLE_PATH=$CHROMIUM_PATH

# Instalar Puppeteer globalmente si no está instalado
if ! npm list -g puppeteer &> /dev/null
then
    echo "📦 Instalando Puppeteer globalmente..."
    npm install -g puppeteer
else
    echo "✅ Puppeteer ya está instalado globalmente."
fi

# Descargar la versión específica de Chrome que Puppeteer requiere
npx puppeteer browsers install chrome-headless-shell

# Verificar que Puppeteer funcione correctamente
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

node test_puppeteer.js

# Limpiar archivo de prueba
rm test_puppeteer.js

echo "✅ Configuración de Puppeteer y Chromium completada con éxito."