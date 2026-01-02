# Configuración de Puppeteer/Browsershot

## Ubicación de Chromium

Chromium se instala automáticamente en:
```
node_modules/puppeteer/node_modules/puppeteer/.local-chromium/chrome/linux-XXX/chrome-linux64/chrome
```

✅ Esto está **incluido en node_modules**, por lo que:
- Se instalará automáticamente con `npm install`
- No necesita configuración adicional
- Queda integrado con las dependencias del proyecto
- No se commitea a Git (ignorado por `/node_modules` en `.gitignore`)

## Instalación Automática (Recomendado)

### Desde la Interfaz Web

1. Ir a **Configuración del Sistema** → **Servicio Técnico**
2. Hacer clic en **"Instalar Puppeteer y Browsershot"**
3. El sistema automáticamente:
   - ✅ Detecta Node.js y npm en el sistema
   - ✅ Instala Puppeteer con Chromium
   - ✅ Detecta la ruta de Chromium instalado
   - ✅ **Actualiza automáticamente el archivo `.env`** con:
     ```env
     NODE_BINARY_PATH=/ruta/a/node
     NPM_BINARY_PATH=/ruta/a/npm
     CHROME_BINARY_PATH=/ruta/completa/a/chrome
     ```
   - ✅ Limpia la caché de configuración de Laravel

**¡Todo queda configurado automáticamente sin intervención manual!**

## Instalación Manual

En servidores nuevos, simplemente ejecuta:

```bash
cd /home/pablo/cth
npm install
```

Puppeteer descargará Chromium automáticamente (~250MB).

## Configuración

El archivo `.puppeteerrc.cjs` contiene:

```javascript
module.exports = {
    skipChromeHeadlessShellDownload: true,
};
```

Esto evita descargar `chrome-headless-shell` que no necesitamos y que a veces causa errores.

## Motor PDF

Puedes elegir el motor PDF en **Configuración del Equipo** → **Preferencias**:

### mPDF (Recomendado para servidores con restricciones)
- ✅ No requiere Node.js/Chromium
- ✅ Más rápido y menos recursos
- ❌ Soporte CSS limitado

### Browsershot (Requiere Puppeteer/Chromium)
- ✅ Soporte CSS completo
- ✅ Renderiza JavaScript
- ❌ Requiere más recursos
- ❌ Puede tener problemas en servidores restrictivos

## Detección Automática de Rutas

Los exportadores PHP (`EventsHistoryPdfExport`, `StatsPdfExport`, `EventsPdfExport`) detectan automáticamente las rutas de:
- Node.js
- npm  
- Chromium

Buscan en este orden:
1. Instalaciones locales en `node_modules/puppeteer/`
2. Rutas del sistema (`/usr/bin`, `/usr/local/bin`)
3. Versiones NVM (`~/.nvm/versions/node/`)

## Solución de Problemas

### Error: "No se encontró un ejecutable de Chromium válido"

**Solución**:
```bash
rm -rf node_modules/puppeteer
PUPPETEER_SKIP_CHROME_HEADLESS_SHELL_DOWNLOAD=true npm install puppeteer
```

### Usar mPDF como alternativa

Si Browsershot sigue dando problemas, cambia a mPDF:

```bash
php artisan tinker
DB::table('teams')->update(['pdf_engine' => 'mpdf']);
```

## Estructura de Archivos

```
/home/pablo/cth/
├── .puppeteerrc.cjs              ← Configuración de Puppeteer
├── node_modules/
│   └── puppeteer/
│       └── node_modules/
│           └── puppeteer/
│               └── .local-chromium/
│                   └── chrome/
│                       └── linux-143.0.7499.42/
│                           └── chrome-linux64/
│                               └── chrome  ← Ejecutable de Chromium (253MB)
├── app/
│   └── Exports/
│       ├── EventsHistoryPdfExport.php
│       ├── EventsPdfExport.php
│       └── StatsPdfExport.php
└── package.json
```

## Notas

- Chromium ocupa ~250-300MB
- La primera instalación tarda 2-5 minutos
- Se descarga automáticamente con `npm install`
- No requiere configuración manual de rutas
