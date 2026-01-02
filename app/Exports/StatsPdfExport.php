<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Event;
use App\Traits\Stats\CalculatesDashboardData;
use App\Traits\Stats\CalculatesScheduledData;
use Carbon\Carbon;
use Spatie\Browsershot\Browsershot;

class StatsPdfExport
{
    use CalculatesDashboardData;
    use CalculatesScheduledData;

    protected $user;
    protected $team;
    protected $browsedUser;
    protected $selectedMonth;
    protected $selectedYear;
    protected $eventTypeId;
    protected $fromDate;
    protected $toDate;
    protected $actualUser;

    public function __construct($userId, $teamId, $browsedUserId, $fromDate, $toDate, $eventTypeId = null)
    {
        $this->user = User::find($userId);
        $this->team = $this->user->currentTeam;
        $this->browsedUser = $browsedUserId;
        $this->actualUser = $this->user;
        $this->eventTypeId = $eventTypeId;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        
        // Parse dates to get month and year
        $startDate = Carbon::parse($fromDate);
        $this->selectedMonth = (int) $startDate->month;
        $this->selectedYear = (int) $startDate->year;
    }

    public function generate(): string
    {
        // Force Spanish locale for translations
        app()->setLocale('es');
        
        $pdfEngine = $this->user && $this->user->currentTeam ? $this->user->currentTeam->pdf_engine : 'browsershot';

        if ($pdfEngine === 'mpdf') {
            return $this->generateMpdf();
        }

        return $this->generateBrowsershot();
    }

    protected function generateMpdf(): string
    {
        // Increase PCRE backtrack limit for large HTML content
        ini_set('pcre.backtrack_limit', '5000000');
        
        list($chartData, $elapsedTime) = $this->getData();
        list($scheduledHours, $scheduledDays) = $this->getScheduledData();
        $dashboardData = $this->getDashboardData($scheduledHours, $scheduledDays);

        $browsedUserModel = User::find($this->browsedUser);
        $eventTypes = $this->team->eventTypes;

        $formatHours = function($hoursFloat) {
            $h = floor($hoursFloat);
            $m = round(($hoursFloat - $h) * 60);
            return sprintf('%dh %02dm', $h, $m);
        };

        $totalHoursFmt = $formatHours($this->totalHours ?? 0);
        $totalNetHours = round(($this->totalHours ?? 0) - ($this->totalPauseHours ?? 0), 2);
        $totalNetHoursFmt = $formatHours($totalNetHours);
        $scheduledHoursFmt = $formatHours($scheduledHours);

        $html = view('exports.stats_mpdf', [
            'chartData' => $chartData,
            'dashboardData' => $dashboardData,
            'scheduledHours' => $scheduledHours,
            'scheduledHoursFmt' => $scheduledHoursFmt,
            'scheduledDays' => $scheduledDays,
            'totalHours' => $this->totalHours ?? 0,
            'totalHoursFmt' => $totalHoursFmt,
            'totalPauseHours' => $this->totalPauseHours ?? 0,
            'totalNetHours' => $totalNetHours,
            'totalNetHoursFmt' => $totalNetHoursFmt,
            'totalDays' => $this->totalDays ?? 0,
            'browsedUser' => $browsedUserModel,
            'team' => $this->team,
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
            'eventTypes' => $eventTypes,
            'eventTypeId' => $this->eventTypeId,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
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
                'line' => 0,
            ]
        ]);
        
        $mpdf->WriteHTML($html);
        
        return $mpdf->Output('', 'S');
    }

    protected function generateBrowsershot(): string
    {
        list($chartData, $elapsedTime) = $this->getData();
        list($scheduledHours, $scheduledDays) = $this->getScheduledData();
        $dashboardData = $this->getDashboardData($scheduledHours, $scheduledDays);

        $browsedUserModel = User::find($this->browsedUser);
        $eventTypes = $this->team->eventTypes;

        $formatHours = function($hoursFloat) {
            $h = floor($hoursFloat);
            $m = round(($hoursFloat - $h) * 60);
            return sprintf('%dh %02dm', $h, $m);
        };

        $totalHoursFmt = $formatHours($this->totalHours ?? 0);
        $totalNetHours = round(($this->totalHours ?? 0) - ($this->totalPauseHours ?? 0), 2);
        $totalNetHoursFmt = $formatHours($totalNetHours);
        
        // Format scheduled hours
        $scheduledHoursFmt = $formatHours($scheduledHours);

        $html = view('exports.stats_pdf', [
            'chartData' => $chartData,
            'dashboardData' => $dashboardData,
            'scheduledHours' => $scheduledHours,
            'scheduledHoursFmt' => $scheduledHoursFmt,
            'scheduledDays' => $scheduledDays,
            'totalHours' => $this->totalHours ?? 0,
            'totalHoursFmt' => $totalHoursFmt,
            'totalPauseHours' => $this->totalPauseHours ?? 0,
            'totalNetHours' => $totalNetHours,
            'totalNetHoursFmt' => $totalNetHoursFmt,
            'totalDays' => $this->totalDays ?? 0,
            'browsedUser' => $browsedUserModel,
            'team' => $this->team,
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
            'eventTypes' => $eventTypes,
            'eventTypeId' => $this->eventTypeId,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ])->render();

        $footerText = trans('reports.CTH - Time and Schedule Control') . ' | ' . trans('reports.Page');
        
        // Detect Chromium executable path automatically (same logic as EventsPdfExport)
        $chromePath = $this->detectChromePath();
        $nodePath = $this->detectNodePath();
        $npmPath = $this->detectNpmPath($nodePath);
        
        \Log::info('Configuración de BrowserShot para Stats:', [
            'node_path' => $nodePath,
            'npm_path' => $npmPath,
            'chrome_path' => $chromePath
        ]);

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
        ];

        $nodeBinDir = dirname($nodePath);

        // Crear directorio temporal único para esta instancia
        $tempDir = sys_get_temp_dir() . '/puppeteer_data_' . uniqid();
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        return Browsershot::html($html)
            ->setNodeBinary($nodePath)
            ->setNpmBinary($npmPath)
            ->setIncludePath('$PATH:' . $nodeBinDir)
            ->setOption('executablePath', $chromePath)
            ->setOption('userDataDir', $tempDir) // Definir directorio de usuario temporal y escribible
            ->setOption('env', [
                'XDG_CONFIG_HOME' => $tempDir, // Redirigir config home para evitar errores de permisos
                'XDG_CACHE_HOME' => $tempDir,  // Redirigir cache home
                'HOME' => $tempDir,            // Redirigir HOME como medida definitiva
            ])
            ->setOption('args', $puppeteerArgs)
            ->format('A4')
            ->landscape()
            ->margins(10, 10, 20, 10)
            ->showBackground()
            ->setOption('displayHeaderFooter', true)
            ->setOption('headerTemplate', '<div style="font-size: 8pt; text-align: center; width: 100%; color: #9CA3AF; padding-bottom: 5px; border-bottom: 1px solid #E5E7EB; margin-left: 10px; margin-right: 10px;">' . 
                trans('stats.Statistics Report') . ' - ' . $this->team->name . ' - ' . $browsedUserModel->name . ' ' . $browsedUserModel->family_name1 .
                '</div>')
            ->setOption('footerTemplate', '<div style="font-size: 8pt; text-align: center; width: 100%; color: #9CA3AF; padding-top: 5px;">' . 
                $footerText . ' <span class="pageNumber"></span> ' . trans('reports.of') . ' <span class="totalPages"></span>' .
                '</div>')
            ->pdf();
    }

    protected function detectChromePath(): string
    {
        // 1. PRIORIDAD: Variable de entorno del .env
        $envChromePath = env('CHROME_BINARY_PATH');
        if ($envChromePath && @file_exists($envChromePath) && @is_executable($envChromePath)) {
            return $envChromePath;
        }

        // 2. PRIORIDAD: Rutas locales del proyecto (Puppeteer instalado localmente)
        $localChromePaths = [
            base_path('node_modules/puppeteer/node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome'),
            base_path('node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome'),
            base_path('node_modules/puppeteer/.local-chromium/linux-*/chrome-linux*/chrome'),
            base_path('.cache/puppeteer/chrome/linux-*/chrome-linux*/chrome'),
        ];

        foreach ($localChromePaths as $path) {
            $matches = @glob($path);
            if ($matches && !empty($matches)) {
                foreach ($matches as $match) {
                    if (@file_exists($match) && @is_executable($match)) {
                        return $match;
                    }
                }
            }
        }

        // 3. ÚLTIMA OPCIÓN: Rutas del sistema
        $systemChromePaths = [
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/local/bin/google-chrome',
            '/usr/local/bin/chromium',
            '/snap/bin/chromium',
        ];

        foreach ($systemChromePaths as $path) {
            if (@file_exists($path) && @is_executable($path)) {
                return $path;
            }
        }

        throw new \Exception("No se encontró un ejecutable de Chromium válido.");
    }

    protected function detectNodePath(): string
    {
        // 1. PRIORIDAD: Variable de entorno del .env
        $envNodePath = env('NODE_BINARY_PATH');
        if ($envNodePath && @file_exists($envNodePath) && @is_executable($envNodePath)) {
            return $envNodePath;
        }

        $whichOutput = [];
        $whichReturnVar = null;
        exec("which node 2>/dev/null", $whichOutput, $whichReturnVar);
        
        if ($whichReturnVar === 0 && !empty($whichOutput[0])) {
            return $whichOutput[0];
        }

        $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '/root');
        $commonNodePaths = [
            '/usr/local/bin/node',
            '/usr/bin/node',
            '/bin/node',
            '/opt/node/bin/node',
            $homeDir . '/.nvm/versions/node/v20.19.5/bin/node',
            $homeDir . '/.nvm/versions/node/v18.20.5/bin/node',
            $homeDir . '/.nvm/versions/node/v16.20.2/bin/node',
        ];

        foreach ($commonNodePaths as $path) {
            if (@file_exists($path) && @is_executable($path)) {
                return $path;
            }
        }

        throw new \Exception("No se encontró un ejecutable de Node.js válido.");
    }

    protected function detectNpmPath(string $nodePath): string
    {
        // 1. PRIORIDAD: Variable de entorno del .env
        $envNpmPath = env('NPM_BINARY_PATH');
        if ($envNpmPath && @file_exists($envNpmPath)) {
            return $envNpmPath;
        }

        $npmPath = dirname($nodePath) . '/npm';
        
        if (@file_exists($npmPath)) {
            return $npmPath;
        }
        
        $whichNpmOutput = [];
        $whichNpmReturnVar = null;
        @exec("which npm 2>/dev/null", $whichNpmOutput, $whichNpmReturnVar);
        if ($whichNpmReturnVar === 0 && !empty($whichNpmOutput[0])) {
            return $whichNpmOutput[0];
        }
        
        return 'npm';
    }

    /**
     * Get chart data - adapted from StatsComponent::getData()
     */
    protected function getData(): array
    {
        $start = microtime(true);
        $this->hasData = true;

        $query = Event::with('eventType')
            ->where('user_id', $this->browsedUser)
            ->whereDate('start', '>=', $this->fromDate)
            ->whereDate('end', '<=', $this->toDate);

        if ($this->eventTypeId && $this->eventTypeId !== 'All') {
            $query->where('event_type_id', $this->eventTypeId);
        }

        $events = $query->get();

        $processedEvents = [];
        $daysWithEvents = [];
        $eventTypesInUse = [];

        $teamTimezone = $this->actualUser->currentTeam->timezone ?? config('app.timezone');
        $startDate = Carbon::parse($this->fromDate, $teamTimezone);
        $endDate = Carbon::parse($this->toDate, $teamTimezone);
        
        $holidays = $this->actualUser->currentTeam->holidays()
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'));

        $workingDays = [1, 2, 3, 4, 5];
        $scheduleMeta = $this->actualUser->meta->where('meta_key', 'work_schedule')->first();
        if ($scheduleMeta && $scheduleMeta->meta_value) {
            $schedule = json_decode($scheduleMeta->meta_value, true);
            if (!empty($schedule)) {
                $workingDays = [];
                foreach ($schedule as $slot) {
                    if (!empty($slot['days'])) {
                        foreach ($slot['days'] as $day) {
                            $workingDays[] = (int)$day;
                        }
                    }
                }
                $workingDays = array_unique($workingDays);
            }
        }

        foreach ($events as $event) {
            if (!$event->end || !$event->eventType) continue;

            $eventTypesInUse[$event->eventType->name] = $event->eventType;

            $start_date = Carbon::parse($event->start, 'UTC');
            $end_date = Carbon::parse($event->end, 'UTC');

            for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
                $dayKey = $date->format('d/m');

                if ($event->eventType && !$event->eventType->is_workday_type) {
                     if ($holidays->contains($date->format('Y-m-d'))) {
                         continue;
                     }
                     if (!in_array($date->format('N'), $workingDays)) {
                         continue;
                     }
                }

                if ($event->eventType->is_all_day) {
                     if ($date->gte($end_date)) {
                         continue;
                     }
                     $hours_for_day = 24;
                     $processedEvents[$dayKey][$event->eventType->name] = ($processedEvents[$dayKey][$event->eventType->name] ?? 0) + $hours_for_day;
                     $daysWithEvents[$dayKey] = $date;
                     continue;
                }
                
                $day_start = $date->copy()->startOfDay();
                $day_end = $date->copy()->endOfDay();
                $effective_start = $start_date->max($day_start);
                $effective_end = $end_date->min($day_end);

                if ($effective_start->lt($effective_end)) {
                    $hours_for_day = $effective_start->diffInSeconds($effective_end) / 3600;
                    
                    // If event type is pause, subtract from total; otherwise add
                    if ($event->eventType->is_pause_type) {
                        $processedEvents[$dayKey][$event->eventType->name] = ($processedEvents[$dayKey][$event->eventType->name] ?? 0) - $hours_for_day;
                    } else {
                        $processedEvents[$dayKey][$event->eventType->name] = ($processedEvents[$dayKey][$event->eventType->name] ?? 0) + $hours_for_day;
                    }
                    $daysWithEvents[$dayKey] = $date;
                }
            }
        }

        uasort($daysWithEvents, function ($a, $b) {
            return $a <=> $b;
        });
        $xAxisData = array_keys($daysWithEvents);

        $dailyTypeHours = [];
        foreach ($daysWithEvents as $dayKey => $dateObject) {
            foreach ($eventTypesInUse as $typeName => $eventType) {
                $hours = $processedEvents[$dayKey][$typeName] ?? null;
                $dailyTypeHours[$dayKey][$typeName] = ['hours' => $hours, 'color' => $eventType->color];
            }
        }

        $totalHours = 0;
        $totalPauseHours = 0;
        $dayCountsPerType = [];
        $uniqueDays = [];

        $workdayEventType = $this->actualUser->currentTeam->eventTypes()->where('is_workday_type', true)->first();

        foreach ($dailyTypeHours as $day => $types) {
            if ($workdayEventType) {
                if (isset($types[$workdayEventType->name])) {
                    $totalHours += $types[$workdayEventType->name]['hours'];
                }
            }
            
            // Calculate pause hours separately
            foreach ($types as $typeName => $data) {
                $eventType = $eventTypesInUse[$typeName] ?? null;
                if ($eventType && $eventType->is_pause_type && $data['hours'] < 0) { // Changed > 0 to < 0 because pause hours are stored as negative
                    $totalPauseHours += abs($data['hours']); // Use abs since we stored it as negative
                }
            }
            
            $uniqueDays[$day] = true;

            foreach ($types as $typeName => $data) {
                if (!isset($dayCountsPerType[$typeName])) {
                    $dayCountsPerType[$typeName] = [];
                }
                $dayCountsPerType[$typeName][$day] = true;
            }
        }

        $this->totalHours = round($totalHours, 2);
        $this->totalPauseHours = round($totalPauseHours, 2);

        if ($this->eventTypeId && $this->eventTypeId !== 'All') {
            $this->totalDays = count($dailyTypeHours);
        } else {
            $maxDays = 0;
            foreach ($dayCountsPerType as $typeName => $days) {
                if (count($days) > $maxDays) {
                    $maxDays = count($days);
                }
            }
            $this->totalDays = $maxDays;
        }

        if (empty($dailyTypeHours)) {
            $this->hasData = false;
            return [[], 0];
        }

        $elapsedTime = number_format((microtime(true) - $start) * 1000, 2);

        return [$dailyTypeHours, $elapsedTime];
    }
}
