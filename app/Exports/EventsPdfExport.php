<?php

namespace App\Exports;

use Spatie\Browsershot\Browsershot;

class EventsPdfExport
{
    protected $events;
    protected $team;
    protected $workCenter;
    protected $startDate;
    protected $endDate;

    public function __construct($events, $team, $workCenter = null, $startDate = null, $endDate = null)
    {
        $this->events = $events;
        $this->team = $team;
        $this->workCenter = $workCenter;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function generate(): string
    {
        // Force Spanish locale for translations
        app()->setLocale('es');
        
        $user = auth()->user();
        $pdfEngine = $user && $user->currentTeam ? $user->currentTeam->pdf_engine : 'browsershot';

        if ($pdfEngine === 'mpdf') {
            return $this->generateMpdf();
        }

        return $this->generateBrowsershot();
    }

    protected function generateMpdf(): string
    {
        $totalDuration = $this->calculateTotalDuration();

        $html = view('exports.events_mpdf', [
            'events' => $this->events,
            'team' => $this->team,
            'workCenter' => $this->workCenter,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalDuration' => $totalDuration,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L', // Landscape
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 20,
        ]);

        $footerText = trans('reports.CTH - Time and Schedule Control') . ' | ' . trans('reports.Page') . ' {PAGENO} ' . trans('reports.of') . ' {nbpg}';
        
        $mpdf->SetFooter([
            'odd' => [
                'L' => [
                    'content' => trans('reports.CTH - Time and Schedule Control'),
                    'font-size' => 8,
                    'font-style' => 'R',
                    'color' => '#9CA3AF'
                ],
                'C' => [
                    'content' => '',
                ],
                'R' => [
                    'content' => trans('reports.Page') . ' {PAGENO} ' . trans('reports.of') . ' {nbpg}',
                    'font-size' => 8,
                    'font-style' => 'R',
                    'color' => '#9CA3AF'
                ],
                'line' => 0, // No line above footer
            ]
        ]);
        $mpdf->WriteHTML($html);
        
        return $mpdf->Output('', 'S'); // Return as string
    }

    protected function generateBrowsershot(): string
    {
        $totalDuration = $this->calculateTotalDuration();

        $html = view('exports.events_pdf', [
            'events' => $this->events,
            'team' => $this->team,
            'workCenter' => $this->workCenter,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalDuration' => $totalDuration,
        ])->render();

        $footerText = trans('reports.CTH - Time and Schedule Control') . ' | ' . trans('reports.Page');
        
        // Detectar la ruta del ejecutable de Chromium
        $user = auth()->user();
        $customChromePath = $user && $user->currentTeam ? $user->currentTeam->chrome_path : null;
        
        $chromePath = null;

        if ($customChromePath && file_exists($customChromePath) && is_executable($customChromePath)) {
            $chromePath = $customChromePath;
        } else {
            // Detección automática si no hay ruta personalizada o no es válida
            $chromePath = '/home/sientia/.cache/puppeteer/chrome-headless-shell/linux-142.0.7444.175/chrome-headless-shell-linux64/chrome-headless-shell'; // Usar el binario ligero chrome-headless-shell

            if (!file_exists($chromePath)) {
                $defaultPaths = [
                    // Rutas del sistema
                    '/usr/bin/google-chrome',
                    '/usr/bin/chromium',
                    '/usr/local/bin/google-chrome',
                    '/usr/local/bin/chromium',
                    
                    // Rutas de caché de usuario (Puppeteer)
                    getenv('HOME') . '/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome',
                    '/home/sientia/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome',
                    
                    // Rutas dentro del proyecto (Nuevas)
                    base_path('node_modules/puppeteer/.local-chromium/linux-142.0.7444.175/chrome-linux64/chrome'),
                    base_path('.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome'),
                    base_path('chrome-linux/chrome'),
                    public_path('chrome-linux/chrome'),
                ];

                foreach ($defaultPaths as $path) {
                    if (file_exists($path) && is_executable($path)) {
                        $chromePath = $path;
                        break;
                    }
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
            '--disable-gpu',
            '--disable-background-networking',
            '--enable-features=NetworkService,NetworkServiceInProcess',
            '--disable-features=site-per-process,IsolateOrigins,SpeculativeServiceWorkerStart',
            '--no-zygote',
        ]; // Argumentos adicionales para deshabilitar telemetría y crashpad

        // Log para verificar el contenido HTML
        \Log::info('Contenido HTML para PDF:', ['html' => $html]);

        // Log para verificar la ruta del ejecutable
        \Log::info('Ruta del ejecutable de Chromium:', ['path' => $chromePath]);

        // Log para verificar los argumentos de Puppeteer
        \Log::info('Argumentos de Puppeteer:', ['args' => $puppeteerArgs]);

        // Configuración de Node.js y NPM
        $nodePath = '/home/pablo/.nvm/versions/node/v20.19.5/bin/node';
        $npmPath = '/home/pablo/.nvm/versions/node/v20.19.5/bin/npm';
        $nodeBinDir = dirname($nodePath);

        return Browsershot::html($html)
            ->setNodeBinary($nodePath)
            ->setNpmBinary($npmPath)
            ->setIncludePath('$PATH:' . $nodeBinDir) // Añadir directorio de Node al PATH
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

    protected function calculateTotalDuration(): string
    {
        $totalMinutes = 0;

        foreach ($this->events as $event) {
            $start = \Carbon\Carbon::parse($event->start);
            $end = \Carbon\Carbon::parse($event->end);
            $totalMinutes += $start->diffInMinutes($end);
        }

        return $this->formatDuration($totalMinutes);
    }

    protected function formatDuration(int $minutes): string
    {
        $days = floor($minutes / (24 * 60));
        $hours = floor(($minutes % (24 * 60)) / 60);
        $remainingMinutes = $minutes % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . ' ' . trans_choice('reports.days', $days);
        }

        if ($hours > 0) {
            $parts[] = $hours . ' ' . trans_choice('reports.hours', $hours);
        }

        if ($remainingMinutes > 0 || empty($parts)) {
            $parts[] = $remainingMinutes . ' ' . trans_choice('reports.minutes', $remainingMinutes);
        }

        return implode(', ', $parts);
    }
}
