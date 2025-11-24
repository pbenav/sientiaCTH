<?php

/**
 * Script to initialize Puppeteer dependencies for Browsershot
 * This script installs the necessary Node.js packages for PDF generation
 */

echo "Iniciando instalación...\n";

// Check if Node.js is installed
exec('node --version 2>&1', $nodeVersion, $nodeReturn);
if ($nodeReturn !== 0) {
    echo "Error: Node.js no está instalado. Por favor, instala Node.js primero.\n";
    exit(1);
}

echo "Node.js detectado: " . trim($nodeVersion[0]) . "\n";

// Check if npm is installed
exec('npm --version 2>&1', $npmVersion, $npmReturn);
if ($npmReturn !== 0) {
    echo "Error: npm no está instalado.\n";
    exit(1);
}

echo "npm detectado: " . trim($npmVersion[0]) . "\n";

// Install Puppeteer
echo "Instalando Puppeteer...\n";
exec('npm install puppeteer 2>&1', $output, $returnVar);

if ($returnVar === 0) {
    echo "✓ Puppeteer instalado correctamente.\n";
    echo implode("\n", $output) . "\n";
} else {
    echo "✗ Error al instalar Puppeteer.\n";
    echo implode("\n", $output) . "\n";
    exit(1);
}

echo "\n=== Instalación completada ===\n";
echo "Puppeteer está listo para usar con Browsershot.\n";

exit(0);
