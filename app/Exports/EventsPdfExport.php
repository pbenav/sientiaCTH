<?php

namespace App\Exports;

use Spatie\Browsershot\Browsershot;

class EventsPdfExport
{
    protected $events;

    public function __construct($events)
    {
        $this->events = $events;
    }

    public function generate(): string
    {
        // Force Spanish locale for translations
        app()->setLocale('es');
        
        $html = view('exports.events_pdf', [
            'events' => $this->events
        ])->render();

        $footerText = trans('reports.CTH - Time and Schedule Control') . ' | ' . trans('reports.Page');
        
        // Detectar la ruta del ejecutable de Chromium de forma dinámica
        $chromePath = env('PUPPETEER_EXECUTABLE_PATH');

        if (!$chromePath) {
            $defaultPaths = [
                '/usr/bin/google-chrome',
                '/usr/bin/chromium',
                '/usr/local/bin/google-chrome',
                '/usr/local/bin/chromium',
                getenv('HOME') . '/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome',
                '/home/sientia/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome', // Ruta específica detectada
            ];

            foreach ($defaultPaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    $chromePath = $path;
                    break;
                }
            }
        }

        if (!$chromePath) {
            // Registrar las rutas verificadas para depuración
            $checkedPaths = implode("\n", $defaultPaths);
            throw new \Exception("No se encontró un ejecutable de Chromium válido. Rutas verificadas:\n" . $checkedPaths);
        }

        // Argumentos adicionales para Puppeteer
        $puppeteerArgs = [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--disable-gpu',
            '--no-crashpad', // Deshabilitar crashpad para evitar errores
            '--disable-crash-reporter', // Deshabilitar completamente el crashpad
            '--crash-dumps-dir=/tmp', // Configurar un directorio temporal para crash dumps
            '--disable-breakpad', // Deshabilitar completamente el sistema de informes de fallos
            '--enable-logging=stderr', // Habilitar logs detallados
            '--v=1', // Nivel de verbosidad para obtener más información
        ];

        return Browsershot::html($html)
            ->setOption('executablePath', $chromePath) // Usar la ruta detectada
            ->setOption('args', $puppeteerArgs) // Añadir argumentos adicionales
            ->format('A4')
            ->landscape()
            ->margins(10, 10, 20, 10) // Increased bottom margin for footer
            ->showBackground()
            ->setOption('displayHeaderFooter', true)
            ->setOption('headerTemplate', '<div></div>') // Empty header
            ->setOption('footerTemplate', '<div style="font-size: 8pt; text-align: center; width: 100%; color: #9CA3AF; padding-top: 5px;">' . 
                $footerText . ' <span class="pageNumber"></span> ' . trans('reports.of') . ' <span class="totalPages"></span>' .
                '</div>')
            ->pdf();
    }
}
