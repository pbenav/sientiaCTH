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
        
        // Detectar la ruta del ejecutable de Chromium automáticamente
        $chromePath = null;
        
        // Rutas comunes de Chrome/Chromium
        $defaultChromePaths = [
            // Ruta preferida: chrome-headless-shell ligero
            '/home/sientia/.cache/puppeteer/chrome-headless-shell/linux-142.0.7444.175/chrome-headless-shell-linux64/chrome-headless-shell',
            
            // Rutas del sistema
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/local/bin/google-chrome',
            '/usr/local/bin/chromium',
            
            // Rutas de caché de usuario (Puppeteer)
            getenv('HOME') . '/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome',
            '/home/sientia/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome',
            
            // Rutas dentro del proyecto
            base_path('node_modules/puppeteer/.local-chromium/linux-142.0.7444.175/chrome-linux64/chrome'),
            base_path('.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome'),
            base_path('chrome-linux/chrome'),
            public_path('chrome-linux/chrome'),
        ];

        foreach ($defaultChromePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                $chromePath = $path;
                break;
            }
        }

        if (!$chromePath) {
            $checkedPaths = implode("\n", $defaultChromePaths);
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

        // Configuración de Node.js automática
        $nodePath = null;

        // Método 1: Usar 'which node' (más confiable)
        $whichOutput = [];
        $whichReturnVar = null;
        exec("which node 2>/dev/null", $whichOutput, $whichReturnVar);
        if ($whichReturnVar === 0 && !empty($whichOutput[0]) && file_exists($whichOutput[0])) {
            $nodePath = $whichOutput[0];
            \Log::info('Node.js encontrado vía which:', ['path' => $nodePath]);
        }

        // Método 2: Buscar en rutas comunes si 'which' no funciona
        if (!$nodePath) {
            $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '/root'); // Fallback para diferentes sistemas
            
            $commonNodePaths = [
                '/usr/local/bin/node',
                '/usr/bin/node',
                '/bin/node',
                '/opt/node/bin/node',
                $homeDir . '/.nvm/versions/node/v20.19.5/bin/node',
                $homeDir . '/.nvm/versions/node/v18.20.5/bin/node',
                $homeDir . '/.nvm/versions/node/v16.20.2/bin/node',
            ];
            
            // Buscar dinámicamente en todas las versiones de NVM si el directorio existe
            $nvmDir = $homeDir . '/.nvm/versions/node';
            if (is_dir($nvmDir)) {
                $nvmVersions = scandir($nvmDir);
                foreach ($nvmVersions as $version) {
                    if ($version !== '.' && $version !== '..') {
                        $commonNodePaths[] = $nvmDir . '/' . $version . '/bin/node';
                    }
                }
            }
            
            foreach ($commonNodePaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    $nodePath = $path;
                    \Log::info('Node.js encontrado en ruta común:', ['path' => $nodePath]);
                    break;
                }
            }
        }

        if (!$nodePath) {
            $errorMsg = "No se encontró un ejecutable de Node.js válido.\n\n";
            $errorMsg .= "Sugerencias:\n";
            $errorMsg .= "1. Instale Node.js: apt-get install nodejs (Ubuntu/Debian) o yum install nodejs (CentOS/RedHat)\n";
            $errorMsg .= "2. Añada Node.js al PATH del sistema\n";
            $errorMsg .= "3. Configure NODE_BINARY_PATH en el archivo .env\n";
            throw new \Exception($errorMsg);
        }

        // Configuración de NPM (derivada de Node.js)
        $npmPath = dirname($nodePath) . '/npm';
        
        // Si no existe en el mismo directorio que node, intentar 'which npm'
        if (!file_exists($npmPath)) {
            $whichNpmOutput = [];
            $whichNpmReturnVar = null;
            exec("which npm 2>/dev/null", $whichNpmOutput, $whichNpmReturnVar);
            if ($whichNpmReturnVar === 0 && !empty($whichNpmOutput[0]) && file_exists($whichNpmOutput[0])) {
                $npmPath = $whichNpmOutput[0];
            } else {
                // Fallback a rutas comunes
                $commonNpmPaths = [
                    '/usr/local/bin/npm',
                    '/usr/bin/npm',
                    '/bin/npm',
                    dirname($nodePath) . '/npm',
                ];
                foreach ($commonNpmPaths as $path) {
                    if (file_exists($path)) {
                        $npmPath = $path;
                        break;
                    }
                }
            }
        }
        
        \Log::info('Configuración de BrowserShot:', [
            'node_path' => $nodePath,
            'npm_path' => $npmPath,
            'chrome_path' => $chromePath
        ]);

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
            // Parse as UTC since events are stored in UTC in the database
            $start = \Carbon\Carbon::parse($event->start, 'UTC');
            $end = \Carbon\Carbon::parse($event->end, 'UTC');
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
