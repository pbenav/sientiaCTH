<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamPreferencesController extends Controller
{
    /**
     * Show the team preferences page.
     */
    public function index()
    {
        return view('team.preferences');
    }

    /**
     * Execute the Puppeteer installation script and return log.
     * Only accessible by global administrators.
     */
    public function installDependencies()
    {
        // Verify that the user is a global administrator
        // Verify that the user is a global administrator
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            if (!function_exists('exec')) {
                throw new \Exception('La función exec() está deshabilitada en este servidor.');
            }

            $output = [];
            $returnVar = 0;
            $logMessages = [];
    
            // Detect Node.js path
            $nodePath = $this->findExecutable('node');
            if (!$nodePath) {
                return response()->json([
                    'log' => "Error: No se pudo encontrar Node.js. Por favor, instala Node.js o verifica que esté en el PATH del sistema.",
                    'success' => false,
                ], 404);
            }
            $logMessages[] = "Node.js encontrado en: {$nodePath}";
    
            // Detect npm path
            $npmPath = $this->findExecutable('npm');
            if (!$npmPath) {
                return response()->json([
                    'log' => "Error: No se pudo encontrar npm. Por favor, instala npm o verifica que esté en el PATH del sistema.",
                    'success' => false,
                ], 404);
            }
            $logMessages[] = "npm encontrado en: {$npmPath}";
    
            // Get Node.js version
            // Get Node.js version
            @exec("{$nodePath} --version 2>&1", $nodeVersion, $nodeReturn);
            if ($nodeReturn === 0) {
                $logMessages[] = "Node.js versión: " . trim($nodeVersion[0]);
            }
    
            // Get npm version
            // Get npm version
            @exec("{$npmPath} --version 2>&1", $npmVersion, $npmReturn);
            if ($npmReturn === 0) {
                $logMessages[] = "npm versión: " . trim($npmVersion[0]);
            }
    
            // Get the project root directory
            $projectRoot = base_path();
            $logMessages[] = "\nInstalando Puppeteer en: {$projectRoot}";
    
            // Get the directory containing node binary
            $nodeDir = dirname($nodePath);
            
            // Create a cache directory within the project for npm
            $npmCacheDir = $projectRoot . '/storage/npm-cache';
            if (!is_dir($npmCacheDir)) {
                mkdir($npmCacheDir, 0755, true);
            }
            
            // Set environment variables:
            // - PATH: include node directory so npm can find node
            // - npm_config_cache: use project's storage directory for npm cache
            // - HOME: set to project root to avoid permission issues with user home directory
            $envVars = "PATH={$nodeDir}:/usr/local/bin:/usr/bin:/bin npm_config_cache={$npmCacheDir} HOME={$projectRoot}";
            
            // Install Puppeteer using npm with full path and updated environment
            // Install Puppeteer using npm with full path and updated environment
            @exec("cd {$projectRoot} && {$envVars} {$npmPath} install puppeteer 2>&1", $output, $returnVar);
    
            if ($returnVar === 0) {
                $logMessages[] = "\n✓ Puppeteer instalado correctamente.";
                $logMessages[] = implode("\n", $output);
                $logMessages[] = "\n=== Instalación completada ===";
                $logMessages[] = "Puppeteer está listo para usar con Browsershot.";
            } else {
                $logMessages[] = "\n✗ Error al instalar Puppeteer.";
                $logMessages[] = implode("\n", $output);
            }
    
            return response()->json([
                'log' => implode("\n", $logMessages),
                'success' => $returnVar === 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'log' => "Error crítico: " . $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    /**
     * Find executable path using 'which' command and common paths.
     */
    /**
     * Find executable path using 'which' command and common paths.
     */
    private function findExecutable($command)
    {
        // Try using 'which' command
        // Try using 'which' command
        @exec("which {$command} 2>/dev/null", $output, $returnVar);
        if ($returnVar === 0 && !empty($output[0])) {
            // Verify if it's actually executable (bypass open_basedir if needed)
            if ($this->verifyExecutable($output[0])) {
                return $output[0];
            }
        }

        // Search in common paths
        $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '/root');
        
        $paths = [
            "/usr/local/bin/{$command}",
            "/usr/bin/{$command}",
            "/bin/{$command}",
            "/opt/node/bin/{$command}",
        ];

        // Search in NVM versions
        $nvmDir = $homeDir . '/.nvm/versions/node';
        if (is_dir($nvmDir)) {
            $versions = scandir($nvmDir);
            foreach ($versions as $version) {
                if ($version !== '.' && $version !== '..') {
                    $binary = $nvmDir . '/' . $version . '/bin/' . $command;
                    // We can't easily check file_exists here if restricted, but we can try to run it later
                    $paths[] = $binary;
                }
            }
        }

        foreach ($paths as $path) {
            if ($this->verifyExecutable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Verify if a path is executable, bypassing open_basedir if possible.
     */
    private function verifyExecutable($path)
    {
        $restrictionEncountered = false;

        // First try standard PHP check (suppressed to avoid open_basedir warning)
        try {
            if (@file_exists($path) && @is_executable($path)) {
                return true;
            }
        } catch (\Throwable $e) {
            // Ignore open_basedir restrictions and proceed to fallback
            $restrictionEncountered = true;
        }

        // Fallback: Try to execute it with --version or similar
        // This often works even if open_basedir restricts file access
        $output = [];
        $returnVar = -1;
        @exec("{$path} --version 2>&1", $output, $returnVar);
        
        if ($returnVar === 0) {
            return true;
        }

        // If we encountered a restriction (like open_basedir) but 'which' found the path,
        // we assume it's valid to avoid blocking installation.
        if ($restrictionEncountered) {
            return true;
        }
        
        return false;
    }
    /**
     * Update the team's PDF engine preference.
     */
    public function updatePdfEngine(Request $request)
    {
        $request->validate([
            'pdf_engine' => 'required|in:browsershot,mpdf',
        ]);

        $team = $request->user()->currentTeam;
        
        $team->forceFill([
            'pdf_engine' => $request->pdf_engine,
        ])->save();

        return back()->with('success', __('Preferencias de PDF actualizadas correctamente.'));
    }

    /**
     * Update the team's report preferences.
     */
    public function updateReportPreferences(Request $request)
    {
        $team = $request->user()->currentTeam;
        
        $request->validate([
            'max_report_months' => 'required|integer|min:1|max:' . \App\Models\Team::ABSOLUTE_MAX_REPORT_MONTHS,
            'async_report_threshold_months' => 'nullable|integer|min:1|max:' . \App\Models\Team::ABSOLUTE_MAX_REPORT_MONTHS,
        ]);

        $team->forceFill([
            'max_report_months' => $request->max_report_months,
            'async_report_threshold_months' => $request->async_report_threshold_months,
        ])->save();

        return back()->with('success', __('Report preferences updated successfully.'));
    }
    /**
     * Detect Chrome or Chromium executable path.
     * Only accessible by global administrators.
     */
    public function detectChrome()
    {
        // Verify that the user is a global administrator
        // Verify that the user is a global administrator
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            if (!function_exists('exec')) {
                throw new \Exception('La función exec() está deshabilitada.');
            }

            $paths = [
                '/usr/bin/google-chrome',
                '/usr/bin/chromium',
                '/usr/bin/chromium-browser',
                '/usr/bin/google-chrome-stable',
                '/snap/bin/chromium',
                '/snap/bin/google-chrome',
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            ];
    
            // Try to find using 'which' command (Linux/macOS)
            $commands = ['google-chrome', 'chromium', 'chromium-browser', 'google-chrome-stable'];
            foreach ($commands as $cmd) {
                $output = null;
                $returnVar = null;
                @exec("which $cmd", $output, $returnVar);
                if ($returnVar === 0 && !empty($output[0])) {
                    $paths[] = $output[0];
                }
            }
    
            foreach ($paths as $path) {
                // Use verifyExecutable to handle open_basedir restrictions
                if ($this->verifyExecutable($path)) {
                    return response()->json([
                        'success' => true,
                        'path' => $path,
                        'message' => __('Chrome/Chromium encontrado en: ') . $path,
                    ]);
                }
            }
    
            return response()->json([
                'success' => false,
                'message' => __('No se pudo encontrar Chrome o Chromium automáticamente. Por favor, especifique la ruta manualmente.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detect Node.js executable path.
     * Only accessible by global administrators.
     */
    public function detectNode()
    {
        // Verify that the user is a global administrator
        // Verify that the user is a global administrator
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            if (!function_exists('exec')) {
                throw new \Exception('La función exec() está deshabilitada.');
            }

            // Método 1: Usar 'which node' (más confiable)
            $output = null;
            $returnVar = null;
            @exec("which node 2>/dev/null", $output, $returnVar);
            if ($returnVar === 0 && !empty($output[0])) {
                // Verify executable
                if ($this->verifyExecutable($output[0])) {
                    return response()->json([
                        'success' => true,
                        'path' => $output[0],
                        'message' => __('Node.js encontrado en: ') . $output[0],
                    ]);
                }
            }
    
            // Método 2: Buscar en rutas comunes
            $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '/root');
            
            $paths = [
                '/usr/local/bin/node',
                '/usr/bin/node',
                '/bin/node',
                '/opt/node/bin/node',
            ];
    
            // Buscar dinámicamente en todas las versiones de NVM
            $nvmDir = $homeDir . '/.nvm/versions/node';
            if (is_dir($nvmDir)) {
                $versions = scandir($nvmDir);
                foreach ($versions as $version) {
                    if ($version !== '.' && $version !== '..') {
                        $nodeBinary = $nvmDir . '/' . $version . '/bin/node';
                        if (file_exists($nodeBinary)) {
                            $paths[] = $nodeBinary;
                        }
                    }
                }
            }
    
            foreach ($paths as $path) {
                if ($this->verifyExecutable($path)) {
                    return response()->json([
                        'success' => true,
                        'path' => $path,
                        'message' => __('Node.js encontrado en: ') . $path,
                    ]);
                }
            }
    
            return response()->json([
                'success' => false,
                'message' => __('No se pudo encontrar Node.js automáticamente. Instale Node.js o añádalo al PATH del sistema.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detect NPM executable path.
     * Only accessible by global administrators.
     */
    public function detectNpm()
    {
        // Verify that the user is a global administrator
        // Verify that the user is a global administrator
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            if (!function_exists('exec')) {
                throw new \Exception('La función exec() está deshabilitada.');
            }

            // Primero intentar con 'which npm'
            $output = null;
            $returnVar = null;
            @exec("which npm 2>/dev/null", $output, $returnVar);
            if ($returnVar === 0 && !empty($output[0])) {
                if ($this->verifyExecutable($output[0])) {
                    return response()->json([
                        'success' => true,
                        'path' => $output[0],
                        'message' => __('NPM encontrado en: ') . $output[0],
                    ]);
                }
            }
    
            // Buscar en rutas comunes
            $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '/root');
            
            $paths = [
                '/usr/local/bin/npm',
                '/usr/bin/npm',
                '/bin/npm',
                '/opt/node/bin/npm',
            ];
    
            // Buscar en versiones de NVM
            $nvmDir = $homeDir . '/.nvm/versions/node';
            if (is_dir($nvmDir)) {
                $versions = scandir($nvmDir);
                foreach ($versions as $version) {
                    if ($version !== '.' && $version !== '..') {
                        $npmBinary = $nvmDir . '/' . $version . '/bin/npm';
                        if (file_exists($npmBinary)) {
                            $paths[] = $npmBinary;
                        }
                    }
                }
            }
    
            foreach ($paths as $path) {
                if ($this->verifyExecutable($path)) {
                    return response()->json([
                        'success' => true,
                        'path' => $path,
                        'message' => __('NPM encontrado en: ') . $path,
                    ]);
                }
            }
    
            return response()->json([
                'success' => false,
                'message' => __('No se pudo encontrar NPM automáticamente. Instale NPM o añádalo al PATH del sistema.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}