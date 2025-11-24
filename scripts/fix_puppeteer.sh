#!/bin/bash

# Ruta al binario de Chrome Headless
CHROME_BIN="/home/sientia/.cache/puppeteer/chrome-headless-shell/linux-142.0.7444.175/chrome-headless-shell-linux64/chrome-headless-shell"

# Verificar si Puppeteer está instalado
if ! npm list puppeteer -g --depth=0 > /dev/null 2>&1; then
  echo "Puppeteer no está instalado globalmente. Instalando..."
  npm install -g puppeteer
else
  echo "Puppeteer ya está instalado."
fi

# Verificar si el binario de Chrome Headless existe
if [ ! -f "$CHROME_BIN" ]; then
  echo "El binario de Chrome Headless no se encuentra en la ruta esperada. Reinstalando Puppeteer..."
  npm install -g puppeteer
else
  echo "El binario de Chrome Headless existe."
fi

# Asegurar permisos adecuados para el binario
if [ -f "$CHROME_BIN" ]; then
  echo "Asegurando permisos para el binario de Chrome Headless..."
  chmod +x "$CHROME_BIN"
  echo "Permisos configurados correctamente."
else
  echo "El binario de Chrome Headless sigue sin estar disponible. Verifica manualmente."
fi

# Validar que Puppeteer funciona correctamente
if node -e "const puppeteer = require('puppeteer'); puppeteer.launch().then(browser => browser.close());"; then
  echo "Puppeteer y Chrome Headless están funcionando correctamente."
else
  echo "Error al validar Puppeteer. Verifica la instalación manualmente."
fi