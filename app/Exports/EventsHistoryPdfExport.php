<?php

namespace App\Exports;

use Spatie\Browsershot\Browsershot;

class EventsHistoryPdfExport
{
    protected $history;
    protected $fromDate;
    protected $toDate;

    public function __construct($history, $fromDate, $toDate)
    {
        $this->history = $history;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        // Enrich history with differences
        foreach ($this->history as $record) {
            $record->differences = $this->calculateDiff($record->original_event, $record->modified_event);
        }
    }

    private function calculateDiff($original, $modified)
    {
        $originalData = json_decode($original, true) ?? [];
        $modifiedData = json_decode($modified, true) ?? [];

        if (!is_array($originalData) || !is_array($modifiedData)) {
            return [];
        }

        $differences = [];

        // Check for changes and deletions
        foreach ($originalData as $key => $value) {
            if (array_key_exists($key, $modifiedData)) {
                if ($modifiedData[$key] != $value) {
                    $originalStr = is_array($value) ? json_encode($value) : (string)$value;
                    $modifiedStr = is_array($modifiedData[$key]) ? json_encode($modifiedData[$key]) : (string)$modifiedData[$key];
                    $differences[$key] = [
                        'type' => 'changed',
                        'original' => $originalStr,
                        'modified' => $modifiedStr
                    ];
                }
            } else {
                $originalStr = is_array($value) ? json_encode($value) : (string)$value;
                $differences[$key] = [
                    'type' => 'deleted',
                    'original' => $originalStr,
                    'modified' => null
                ];
            }
        }

        // Check for additions
        foreach ($modifiedData as $key => $value) {
            if (!array_key_exists($key, $originalData)) {
                $modifiedStr = is_array($value) ? json_encode($value) : (string)$value;
                $differences[$key] = [
                    'type' => 'added',
                    'original' => null,
                    'modified' => $modifiedStr
                ];
            }
        }

        return $differences;
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

        try {
            return $this->generateBrowsershot();
        } catch (\Throwable $e) {
            \Log::warning('Browsershot export failed for Audit History, falling back to mPDF.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->generateMpdf();
        }
    }

    protected function generateMpdf(): string
    {
        // Increase PCRE backtrack limit for large HTML content
        ini_set('pcre.backtrack_limit', '5000000');

        $html = view('exports.history_mpdf', [
            'history' => $this->history,
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
            'format' => 'A4-L', // Landscape by default for history
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 20,
            'autoPageBreak' => true,
            'orientation' => 'L',
        ]);
        
        // CRITICAL: Force cells to grow vertically, NEVER shrink font-size
        $mpdf->shrink_tables_to_fit = 0;  // Never shrink tables
        $mpdf->table_error_report = false;  // Don't report table width issues
        $mpdf->autoScriptToLang = false;  // Disable auto language detection
        $mpdf->baseScript = 1;  // Latin script
        $mpdf->autoVietnamese = false;
        $mpdf->autoArabic = false;
        
        // Force table cells to expand vertically
        // Increase PCRE backtrack limit for large HTML content
        ini_set('pcre.backtrack_limit', '5000000');
        $mpdf->useSubstitutions = false;
        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;

        $footerText = __('Audit Log Report') . ' | ' . __('Page') . ' {PAGENO} ' . __('of') . ' {nbpg}';
        
        $mpdf->SetFooter([
            'odd' => [
                'L' => [
                    'content' => __('Audit Log Report'),
                    'font-size' => 8,
                    'font-style' => 'R',
                    'color' => '#9CA3AF'
                ],
                'C' => [
                    'content' => '',
                ],
                'R' => [
                    'content' => $footerText,
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
        $html = view('exports.history_pdf', [
            'history' => $this->history,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ])->render();

        $footerText = __('Audit Log Report') . ' | ' . __('Page');

        // Automatic path detection using robust helper methods
        $chromePath = $this->detectChromePath();
        $nodePath = $this->detectNodePath();
        $npmPath = $this->detectNpmPath($nodePath);

        \Log::info('Audit History Export - BrowserShot Config:', [
            'node_path' => $nodePath,
            'npm_path' => $npmPath,
            'chrome_path' => $chromePath
        ]);

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
            ->setOption('headerTemplate', '<div></div>')
            ->setOption('footerTemplate', '<div style="font-size: 8pt; text-align: center; width: 100%; color: #9CA3AF; padding-top: 5px;">' . 
                $footerText . ' <span class="pageNumber"></span> ' . __('of') . ' <span class="totalPages"></span>' .
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
            // Nueva estructura de Puppeteer (nested node_modules)
            base_path('node_modules/puppeteer/node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome'),
            // Estructura antigua de Puppeteer
            base_path('node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome'),
            base_path('node_modules/puppeteer/.local-chromium/linux-*/chrome-linux*/chrome'),
            // Cache de Puppeteer
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

        // 3. ÚLTIMA OPCIÓN: Rutas del sistema (solo si no hay instalación local)
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

        throw new \Exception("No se encontró un ejecutable de Chromium válido. Por favor, instale Puppeteer con 'npm install puppeteer'.");
    }

    protected function detectNodePath(): string
    {
        // 1. PRIORIDAD: Variable de entorno del .env
        $envNodePath = env('NODE_BINARY_PATH');
        if ($envNodePath && @file_exists($envNodePath) && @is_executable($envNodePath)) {
            return $envNodePath;
        }

        // 2. Usar 'which node'
        $whichOutput = [];
        $whichReturnVar = null;
        exec("which node 2>/dev/null", $whichOutput, $whichReturnVar);
        
        if ($whichReturnVar === 0 && !empty($whichOutput[0])) {
            return $whichOutput[0];
        }

        // 3. Buscar en rutas comunes
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

        // Buscar dinámicamente en todas las versiones de NVM
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

        // 2. Derivar de la ruta de Node.js
        $npmPath = dirname($nodePath) . '/npm';
        
        if (@file_exists($npmPath)) {
            return $npmPath;
        }
        
        // 3. Intentar con 'which npm'
        $whichNpmOutput = [];
        $whichNpmReturnVar = null;
        @exec("which npm 2>/dev/null", $whichNpmOutput, $whichNpmReturnVar);
        if ($whichNpmReturnVar === 0 && !empty($whichNpmOutput[0])) {
            return $whichNpmOutput[0];
        }
        
        // 4. Usar 'npm' como comando genérico
        return 'npm';
    }
}
