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
    protected $groupBy;
    protected $orderBy;

    public function __construct($events, $team, $workCenter = null, $startDate = null, $endDate = null, $groupBy = 'none', $orderBy = 'start')
    {
        $this->events = $events;
        $this->team = $team;
        $this->workCenter = $workCenter;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->groupBy = $groupBy;
        $this->orderBy = $orderBy;
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
        // Increase PCRE backtrack limit for large HTML content
        ini_set('pcre.backtrack_limit', '5000000');

        $totalDuration = $this->calculateTotalDuration();
        $totalRecords = $this->events->count();

        // Start with the collection
        $eventsForView = $this->events;
        
        // Clip events to date range if specified
        if ($this->startDate && $this->endDate) {
            $eventsForView = $eventsForView->map(function($event) {
                return $this->clipEventToDateRange($event);
            });
        }

        // Apply sorting based on orderBy
        if ($this->orderBy === 'user_name') {
            // Sort by user name only
            $eventsForView = $eventsForView->sortBy(function ($event) {
                $name = $event->user->name ?? '';
                $family1 = $event->user->family_name1 ?? '';
                $family2 = $event->user->family_name2 ?? '';
                return strtolower(trim($name . ' ' . $family1 . ' ' . $family2));
            })->values();
        } else {
            // Sort by date only
            $eventsForView = $eventsForView->sortBy('start')->values();
        }

        // Grouping logic
        if ($this->groupBy === 'user') {
            $eventsForView = $eventsForView->groupBy(function($event) {
                $name = $event->user->name ?? '';
                $family1 = $event->user->family_name1 ?? '';
                $family2 = $event->user->family_name2 ?? '';
                return trim($name . ' ' . $family1 . ' ' . $family2);
            });
        } elseif ($this->groupBy === 'date') {
            $eventsForView = $eventsForView->groupBy(function($event) {
                return \Carbon\Carbon::parse($event->start)->format('d/m/Y');
            });
        }

        $html = view('exports.events_mpdf', [
            'events' => $eventsForView,
            'team' => $this->team,
            'workCenter' => $this->workCenter,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalDuration' => $totalDuration,
            'groupBy' => $this->groupBy,
            'totalRecords' => $totalRecords,
        ])->render();

        // Ensure temp directory exists and is writable
        $tempDir = storage_path('app/mpdf');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => $tempDir,
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
        $totalRecords = $this->events->count();

        // Start with the collection
        $eventsForView = $this->events;
        
        // Clip events to date range if specified
        if ($this->startDate && $this->endDate) {
            $eventsForView = $eventsForView->map(function($event) {
                return $this->clipEventToDateRange($event);
            });
        }

        // Apply sorting based on orderBy
        if ($this->orderBy === 'user_name') {
            // Sort by user name only
            $eventsForView = $eventsForView->sortBy(function ($event) {
                $name = $event->user->name ?? '';
                $family1 = $event->user->family_name1 ?? '';
                $family2 = $event->user->family_name2 ?? '';
                return strtolower(trim($name . ' ' . $family1 . ' ' . $family2));
            })->values();
        } else {
            // Sort by date only
            $eventsForView = $eventsForView->sortBy('start')->values();
        }

        // Grouping logic
        if ($this->groupBy === 'user') {
            $eventsForView = $eventsForView->groupBy(function($event) {
                $name = $event->user->name ?? '';
                $family1 = $event->user->family_name1 ?? '';
                $family2 = $event->user->family_name2 ?? '';
                return trim($name . ' ' . $family1 . ' ' . $family2);
            });
        } elseif ($this->groupBy === 'date') {
            $eventsForView = $eventsForView->groupBy(function($event) {
                return \Carbon\Carbon::parse($event->start)->format('d/m/Y');
            });
        }

        $html = view('exports.events_pdf', [
            'events' => $eventsForView,
            'team' => $this->team,
            'workCenter' => $this->workCenter,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalDuration' => $totalDuration,
            'groupBy' => $this->groupBy,
            'totalRecords' => $totalRecords,
        ])->render();

        $footerText = trans('reports.CTH - Time and Schedule Control') . ' | ' . trans('reports.Page');
        
        // Detectar la ruta del ejecutable de Chromium automáticamente
        $chromePath = null;
        
        // 1. PRIORIDAD: Variable de entorno del .env
        $envChromePath = env('CHROME_BINARY_PATH');
        if ($envChromePath && @file_exists($envChromePath) && @is_executable($envChromePath)) {
            $chromePath = $envChromePath;
        } else {
            // 2. PRIORIDAD: Rutas locales del proyecto
            $defaultChromePaths = [
                // Nueva estructura de Puppeteer (nested node_modules)
                base_path('node_modules/puppeteer/node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome'),
                // Estructura antigua
                base_path('node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome'),
                base_path('node_modules/puppeteer/.local-chromium/linux-*/chrome-linux*/chrome'),
                base_path('.cache/puppeteer/chrome/linux-*/chrome-linux*/chrome'),
                
                // 3. Rutas del sistema (última opción)
                '/usr/bin/google-chrome',
                '/usr/bin/chromium',
                '/usr/bin/chromium-browser',
                '/usr/local/bin/google-chrome',
                '/usr/local/bin/chromium',
                '/snap/bin/chromium',
            ];

            foreach ($defaultChromePaths as $path) {
                // Usar glob para rutas con comodines
                if (strpos($path, '*') !== false) {
                    $matches = @glob($path);
                    if ($matches && !empty($matches)) {
                        foreach ($matches as $match) {
                            if (@file_exists($match) && @is_executable($match)) {
                                $chromePath = $match;
                                break 2;
                            }
                        }
                    }
                } else {
                    if (@file_exists($path) && @is_executable($path)) {
                        $chromePath = $path;
                        break;
                    }
                }
            }
        }

        if (!$chromePath) {
            throw new \Exception("No se encontró un ejecutable de Chromium válido. Por favor, ejecuta el instalador de Puppeteer desde Configuración del Sistema.");
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
            '--pipe',
            '--disable-crash-reporter',
            '--disable-breakpad',
            '--disable-client-side-phishing-detection',
            '--disable-component-extensions-with-background-pages',
            '--disable-default-apps',
            '--disable-extensions',
        ]; // Argumentos adicionales para deshabilitar telemetría y crashpad

        // Log para verificar el contenido HTML
        \Log::info('Contenido HTML para PDF:', ['html' => substr($html, 0, 100) . '...']);

        // Log para verificar la ruta del ejecutable
        \Log::info('Ruta del ejecutable de Chromium:', ['path' => $chromePath]);

        // Configuración de Node.js automática
        $nodePath = null;

        // Método 1: Usar 'which node' (más confiable)
        $whichOutput = [];
        $whichReturnVar = null;
        exec("which node 2>/dev/null", $whichOutput, $whichReturnVar);
        
        // Si 'which' devuelve algo, lo usamos directamente.
        if ($whichReturnVar === 0 && !empty($whichOutput[0])) {
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
                $nvmVersions = @scandir($nvmDir);
                if ($nvmVersions) {
                    foreach ($nvmVersions as $version) {
                        if ($version !== '.' && $version !== '..') {
                            $commonNodePaths[] = $nvmDir . '/' . $version . '/bin/node';
                        }
                    }
                }
            }
            
            foreach ($commonNodePaths as $path) {
                if (@file_exists($path) && @is_executable($path)) {
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
        
        // Intentar verificar si existe (puede fallar con open_basedir)
        if (!@file_exists($npmPath)) {
            // Intentar 'which npm'
            $whichNpmOutput = [];
            $whichNpmReturnVar = null;
            @exec("which npm 2>/dev/null", $whichNpmOutput, $whichNpmReturnVar);
            if ($whichNpmReturnVar === 0 && !empty($whichNpmOutput[0])) {
                $npmPath = $whichNpmOutput[0];
            } else {
                // Si no se puede detectar, usar 'npm' como comando genérico
                // Esto funcionará si NPM está en el PATH del proceso PHP
                $npmPath = 'npm';
            }
        }
        
        \Log::info('Configuración de BrowserShot:', [
            'node_path' => $nodePath,
            'npm_path' => $npmPath,
            'chrome_path' => $chromePath
        ]);

        $nodeBinDir = dirname($nodePath);

        // Crear directorio temporal único para esta instancia
        $tempDir = sys_get_temp_dir() . '/puppeteer_data_' . uniqid();
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        return Browsershot::html($html)
            ->setNodeBinary($nodePath)
            ->setNpmBinary($npmPath)
            ->setIncludePath('$PATH:' . $nodeBinDir) // Añadir directorio de Node al PATH
            ->setOption('executablePath', $chromePath) // Usar la ruta detectada
            ->setOption('userDataDir', $tempDir) // Definir directorio de usuario temporal y escribible
            ->setOption('env', [
                'XDG_CONFIG_HOME' => $tempDir, // Redirigir config home para evitar errores de permisos
                'XDG_CACHE_HOME' => $tempDir,  // Redirigir cache home
                'HOME' => $tempDir,            // Redirigir HOME como medida definitiva
            ])
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
        $totalDays = 0;
        $totalMinutes = 0;

        // Get the clipped events (same as what's displayed)
        $eventsForCalculation = $this->events;
        
        if ($this->startDate && $this->endDate) {
            $eventsForCalculation = $eventsForCalculation->map(function($event) {
                return $this->clipEventToDateRange($event);
            });
        }

        foreach ($eventsForCalculation as $event) {
            // Check if it's an all-day event
            if ($event->eventType && $event->eventType->is_all_day) {
                // For all-day events, use getPeriodForUser to respect user preference
                $period = $event->getPeriodForUser($event->user);
                // Extract number from string like "3 días" or "1 día"
                if (preg_match('/(\d+)/', $period, $matches)) {
                    $totalDays += (int)$matches[1];
                }
            } else {
                // For regular events, calculate minutes
                $start = \Carbon\Carbon::parse($event->start, 'UTC');
                $end = \Carbon\Carbon::parse($event->end, 'UTC');
                $totalMinutes += $start->diffInMinutes($end);
            }
        }

        // Convert total minutes to days/hours/minutes if there are any
        if ($totalMinutes > 0) {
            $days = floor($totalMinutes / 1440);
            $hours = floor(($totalMinutes % 1440) / 60);
            $minutes = $totalMinutes % 60;
            
            $totalDays += $days;
            
            return $this->formatDuration($totalDays * 1440 + $hours * 60 + $minutes);
        }

        // If only all-day events, return just days
        return $totalDays . ' ' . ($totalDays == 1 ? __('day') : __('days'));
    }


    /**
     * Clip an event's dates to the report date range.
     * Returns a cloned event with adjusted start/end dates.
     */
    protected function clipEventToDateRange($event)
    {
        $clonedEvent = clone $event;
        
        if (!$this->startDate || !$this->endDate) {
            return $clonedEvent;
        }
        
        $teamTimezone = $this->team->timezone ?? 'UTC';
        
        // Parse event dates in team timezone
        $eventStart = \Carbon\Carbon::parse($event->start, 'UTC')->setTimezone($teamTimezone);
        $eventEnd = \Carbon\Carbon::parse($event->end, 'UTC')->setTimezone($teamTimezone);
        
        // Parse range boundaries
        $rangeStart = \Carbon\Carbon::parse($this->startDate, $teamTimezone)->startOfDay();
        $rangeEnd = \Carbon\Carbon::parse($this->endDate, $teamTimezone)->endOfDay();
        
        // Clip start date if event starts before range
        if ($eventStart->lt($rangeStart)) {
            $eventStart = $rangeStart;
        }
        
        // Clip end date if event ends after range
        if ($eventEnd->gt($rangeEnd)) {
            $eventEnd = $rangeEnd;
        }
        
        // Convert back to UTC for storage
        $clonedEvent->start = $eventStart->copy()->setTimezone('UTC')->toDateTimeString();
        $clonedEvent->end = $eventEnd->copy()->setTimezone('UTC')->toDateTimeString();
        
        return $clonedEvent;
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
