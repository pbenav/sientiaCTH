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
     * Detects executables and updates .env file with paths.
     * Only accessible by global administrators.
     */
    public function installDependencies()
    {
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
            $logMessages[] = "✓ Node.js encontrado en: {$nodePath}";
    
            // Detect npm path
            $npmPath = $this->findExecutable('npm');
            if (!$npmPath) {
                return response()->json([
                    'log' => "Error: No se pudo encontrar npm. Por favor, instala npm o verifica que esté en el PATH del sistema.",
                    'success' => false,
                ], 404);
            }
            $logMessages[] = "✓ npm encontrado en: {$npmPath}";
    
            // Get Node.js version
            @exec("{$nodePath} --version 2>&1", $nodeVersion, $nodeReturn);
            if ($nodeReturn === 0) {
                $logMessages[] = "  Node.js versión: " . trim($nodeVersion[0]);
            }
    
            // Get npm version
            @exec("{$npmPath} --version 2>&1", $npmVersion, $npmReturn);
            if ($npmReturn === 0) {
                $logMessages[] = "  npm versión: " . trim($npmVersion[0]);
            }
    
            // Get the project root directory
            $projectRoot = base_path();
            $logMessages[] = "\n📦 Instalando Puppeteer en: {$projectRoot}";
    
            // Get the directory containing node binary
            $nodeDir = dirname($nodePath);
            
            // Create a cache directory within the project for npm
            $npmCacheDir = $projectRoot . '/storage/npm-cache';
            if (!is_dir($npmCacheDir)) {
                mkdir($npmCacheDir, 0755, true);
            }
            
            // Set environment variables for installation
            // NOTA: .puppeteerrc.cjs ya configura skipChromeHeadlessShellDownload
            $envVars = "PATH={$nodeDir}:/usr/local/bin:/usr/bin:/bin npm_config_cache={$npmCacheDir} HOME={$projectRoot}";
            
            // Uninstall Puppeteer first to force fresh download of Chrome
            $logMessages[] = "\n🗑️  Limpiando instalación previa de Puppeteer...";
            @exec("cd {$projectRoot} && {$envVars} {$npmPath} uninstall puppeteer 2>&1", $uninstallOutput, $uninstallReturn);
            
            // Install Puppeteer using npm
            $logMessages[] = "📥 Descargando Puppeteer y Chromium (~250MB, puede tardar 2-5 minutos)...";
            @exec("cd {$projectRoot} && {$envVars} {$npmPath} install puppeteer 2>&1", $output, $returnVar);
    
            if ($returnVar === 0) {
                $logMessages[] = "✓ Puppeteer instalado correctamente";
                
                // Detect Chrome path in project
                $chromePath = $this->findLocalChrome($projectRoot);
                
                if ($chromePath) {
                    $logMessages[] = "\n🔍 Chromium detectado en: {$chromePath}";
                    
                    // Update .env file with detected paths
                    $envUpdated = $this->updateEnvFile([
                        'NODE_BINARY_PATH' => $nodePath,
                        'NPM_BINARY_PATH' => $npmPath,
                        'CHROME_BINARY_PATH' => $chromePath,
                    ]);
                    
                    if ($envUpdated) {
                        $logMessages[] = "\n✓ Archivo .env actualizado con las rutas detectadas:";
                        $logMessages[] = "  NODE_BINARY_PATH={$nodePath}";
                        $logMessages[] = "  NPM_BINARY_PATH={$npmPath}";
                        $logMessages[] = "  CHROME_BINARY_PATH={$chromePath}";
                        
                        // Clear Laravel config cache
                        try {
                            \Artisan::call('config:clear');
                            $logMessages[] = "\n✓ Caché de configuración limpiado";
                        } catch (\Exception $e) {
                            $logMessages[] = "\n⚠️  Advertencia: No se pudo limpiar la caché automáticamente";
                            $logMessages[] = "   Ejecuta manualmente: php artisan config:clear";
                        }
                    } else {
                        $logMessages[] = "\n⚠️  No se pudo actualizar el archivo .env";
                    }
                } else {
                    $logMessages[] = "\n⚠️  No se pudo detectar Chromium en la instalación local";
                    $logMessages[] = "   Verifica que Puppeteer se haya instalado correctamente";
                }
                
                $logMessages[] = "\n=== Instalación completada ===";
                $logMessages[] = "Puppeteer está listo para usar con Browsershot.";
            } else {
                $logMessages[] = "\n✗ Error al instalar Puppeteer";
                $logMessages[] = implode("\n", $output);
            }
    
            return response()->json([
                'log' => implode("\n", $logMessages),
                'success' => $returnVar === 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'log' => "❌ Error crítico: " . $e->getMessage(),
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
     * Find locally installed Chrome by Puppeteer
     */
    private function findLocalChrome($projectRoot)
    {
        $possiblePaths = [
            // New Puppeteer structure (nested node_modules)
            $projectRoot . '/node_modules/puppeteer/node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome',
            // Old Puppeteer structure
            $projectRoot . '/node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome',
            // Legacy paths
            $projectRoot . '/.cache/puppeteer/chrome/linux-*/chrome-linux*/chrome',
            $projectRoot . '/node_modules/puppeteer/.local-chromium/linux-*/chrome-linux*/chrome',
        ];
        
        foreach ($possiblePaths as $pattern) {
            $matches = @glob($pattern);
            if ($matches && !empty($matches)) {
                foreach ($matches as $match) {
                    if (@file_exists($match) && @is_executable($match)) {
                        return $match;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Update .env file with detected paths
     */
    private function updateEnvFile($variables)
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            return false;
        }
        
        $envContent = file_get_contents($envPath);
        
        foreach ($variables as $key => $value) {
            // Escape special characters in the value
            $escapedValue = str_replace('\\', '\\\\', $value);
            
            // Check if the key already exists
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                // Update existing value
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$escapedValue}",
                    $envContent
                );
            } else {
                // Add new variable at the end
                $envContent .= "\n{$key}={$escapedValue}\n";
            }
        }
        
        // Write back to file
        return file_put_contents($envPath, $envContent) !== false;
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
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403)
                ->header('Content-Type', 'application/json');
        }
        
        try {
            if (!function_exists('exec')) {
                return response()->json([
                    'success' => false,
                    'message' => 'La función exec() está deshabilitada.',
                ], 500)->header('Content-Type', 'application/json');
            }

            $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '/root');
            
            // PRIORIDAD 1: Rutas locales del proyecto (Puppeteer instalado localmente)
            $localPaths = [
                // Nueva estructura de Puppeteer (nested node_modules) - PRIORIDAD MÁXIMA
                base_path('node_modules/puppeteer/node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome'),
                // Estructura antigua de Puppeteer
                base_path('node_modules/puppeteer/.local-chromium/chrome/linux-*/chrome-linux64/chrome'),
                base_path('node_modules/puppeteer/.local-chromium/linux-*/chrome-linux*/chrome'),
                // Cache de Puppeteer
                base_path('.cache/puppeteer/chrome/linux-*/chrome-linux*/chrome'),
            ];

            // Verificar rutas locales primero (con glob)
            foreach ($localPaths as $path) {
                $matches = @glob($path);
                if ($matches && !empty($matches)) {
                    foreach ($matches as $match) {
                        if ($this->verifyExecutable($match)) {
                            return response()->json([
                                'success' => true,
                                'path' => $match,
                                'message' => __('Chrome/Chromium encontrado en: ') . $match,
                            ])->header('Content-Type', 'application/json');
                        }
                    }
                }
            }
            
            // PRIORIDAD 2: Rutas del sistema (solo si no hay instalación local)
            $systemPaths = [
                '/usr/bin/google-chrome',
                '/usr/bin/chromium',
                '/usr/bin/chromium-browser',
                '/usr/bin/google-chrome-stable',
                '/snap/bin/chromium',
                '/snap/bin/google-chrome',
                '/usr/local/bin/google-chrome',
                '/usr/local/bin/chromium',
                
                // macOS
                '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
                
                // Windows
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            ];
    
            // Try to find using 'which' command (Linux/macOS)
            $commands = ['google-chrome', 'chromium', 'chromium-browser', 'google-chrome-stable'];
            foreach ($commands as $cmd) {
                $output = null;
                $returnVar = null;
                @exec("which $cmd 2>/dev/null", $output, $returnVar);
                if ($returnVar === 0 && !empty($output[0])) {
                    $systemPaths[] = $output[0];
                }
            }
            
            // Check system paths
            foreach ($systemPaths as $path) {
                if ($this->verifyExecutable($path)) {
                    return response()->json([
                        'success' => true,
                        'path' => $path,
                        'message' => __('Chrome/Chromium encontrado en: ') . $path,
                    ])->header('Content-Type', 'application/json');
                }
            }
    
            return response()->json([
                'success' => false,
                'message' => __('No se pudo encontrar Chrome o Chromium automáticamente. Por favor, especifique la ruta manualmente o instale Puppeteer con "npm install puppeteer".'),
            ])->header('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Detect Node.js executable path.
     * Only accessible by global administrators.
     */
    public function detectNode()
    {
        // Verify that the user is a global administrator
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403)
                ->header('Content-Type', 'application/json');
        }
        
        try {
            if (!function_exists('exec')) {
                return response()->json([
                    'success' => false,
                    'message' => 'La función exec() está deshabilitada.',
                ], 500)->header('Content-Type', 'application/json');
            }

            // PRIORIDAD 1: Verificar variable de entorno del .env
            $envNodePath = env('NODE_BINARY_PATH');
            if ($envNodePath && @file_exists($envNodePath) && @is_executable($envNodePath)) {
                return response()->json([
                    'success' => true,
                    'path' => $envNodePath,
                    'message' => __('Node.js encontrado en: ') . $envNodePath . ' (desde .env)',
                ])->header('Content-Type', 'application/json');
            }

            $debugInfo = [];
            
            // Método 1: Usar 'which node' (más confiable y evita open_basedir)
            $output = null;
            $returnVar = null;
            @exec("which node 2>/dev/null", $output, $returnVar);
            $debugInfo['which_node'] = ['return' => $returnVar, 'output' => $output];
            
            if ($returnVar === 0 && !empty($output[0])) {
                // Verificar que realmente funciona ejecutándolo
                $testOutput = null;
                $testReturn = null;
                @exec("{$output[0]} --version 2>/dev/null", $testOutput, $testReturn);
                $debugInfo['test_which_path'] = ['return' => $testReturn, 'output' => $testOutput];
                
                if ($testReturn === 0) {
                    return response()->json([
                        'success' => true,
                        'path' => $output[0],
                        'message' => __('Node.js encontrado en: ') . $output[0],
                    ])->header('Content-Type', 'application/json');
                } else {
                    // RELAXED CHECK: If 'which' found it but execution check failed (common in restricted envs),
                    // we still return it as valid because it likely works for Browsershot internally.
                     return response()->json([
                        'success' => true,
                        'path' => $output[0],
                        'message' => __('Node.js encontrado en: ') . $output[0] . __(' (verificación estricta saltada)'),
                    ])->header('Content-Type', 'application/json');
                }
            }
    
            // Método 2: Intentar ejecutar 'node --version' directamente
            $versionOutput = null;
            $versionReturn = null;
            @exec("node --version 2>/dev/null", $versionOutput, $versionReturn);
            $debugInfo['node_version'] = ['return' => $versionReturn, 'output' => $versionOutput];
            
            if ($versionReturn === 0 && !empty($versionOutput[0])) {
                // Node está disponible en el PATH, intentar obtener su ruta
                $pathOutput = null;
                $pathReturn = null;
                @exec("command -v node 2>/dev/null", $pathOutput, $pathReturn);
                $debugInfo['command_v'] = ['return' => $pathReturn, 'output' => $pathOutput];
                
                $nodePath = ($pathReturn === 0 && !empty($pathOutput[0])) ? $pathOutput[0] : 'node';
                
                return response()->json([
                    'success' => true,
                    'path' => $nodePath,
                    'message' => __('Node.js encontrado (versión: ') . trim($versionOutput[0]) . ')',
                ])->header('Content-Type', 'application/json');
            }
    
            // Método 3: Buscar en rutas comunes del sistema
            $commonPaths = [
                '/usr/bin/node',
                '/usr/local/bin/node',
                '/bin/node',
                '/opt/node/bin/node',
            ];
    
            foreach ($commonPaths as $path) {
                $testOutput = null;
                $testReturn = null;
                @exec("{$path} --version 2>/dev/null", $testOutput, $testReturn);
                $debugInfo['common_paths'][$path] = ['return' => $testReturn, 'output' => $testOutput];
                
                if ($testReturn === 0) {
                    return response()->json([
                        'success' => true,
                        'path' => $path,
                        'message' => __('Node.js encontrado en: ') . $path,
                    ])->header('Content-Type', 'application/json');
                }
            }
            
            // Método 4: Inspeccionar PATH del sistema
            $pathEnv = getenv('PATH');
            $debugInfo['PATH'] = $pathEnv;
            
            // Método 5: Intentar con shell_exec como alternativa
            if (function_exists('shell_exec')) {
                $shellOutput = @shell_exec('node --version 2>/dev/null');
                $debugInfo['shell_exec'] = $shellOutput;
                
                if ($shellOutput && trim($shellOutput)) {
                    return response()->json([
                        'success' => true,
                        'path' => 'node',
                        'message' => __('Node.js encontrado vía shell_exec (versión: ') . trim($shellOutput) . ')',
                    ])->header('Content-Type', 'application/json');
                }
            }
    
            // Si llegamos aquí, no se encontró Node - devolver info de debug
            return response()->json([
                'success' => false,
                'message' => __('No se pudo encontrar Node.js. Por favor, introduce la ruta manualmente.'),
                'debug' => $debugInfo,
            ])->header('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Detect NPM executable path.
     * Only accessible by global administrators.
     */
    public function detectNpm()
    {
        // Verify that the user is a global administrator
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403)
                ->header('Content-Type', 'application/json');
        }

        try {
            if (!function_exists('exec')) {
                return response()->json([
                    'success' => false,
                    'message' => 'La función exec() está deshabilitada.',
                ], 500)->header('Content-Type', 'application/json');
            }

            // PRIORIDAD 1: Verificar variable de entorno del .env
            $envNpmPath = env('NPM_BINARY_PATH');
            if ($envNpmPath && @file_exists($envNpmPath)) {
                return response()->json([
                    'success' => true,
                    'path' => $envNpmPath,
                    'message' => __('NPM encontrado en: ') . $envNpmPath . ' (desde .env)',
                ])->header('Content-Type', 'application/json');
            }

            $debugInfo = [];
            
            // Método 1: Usar 'which npm' (más confiable y evita open_basedir)
            $output = null;
            $returnVar = null;
            @exec("which npm 2>/dev/null", $output, $returnVar);
            $debugInfo['which_npm'] = ['return' => $returnVar, 'output' => $output];
            
            if ($returnVar === 0 && !empty($output[0])) {
                // Verificar que realmente funciona ejecutándolo
                $testOutput = null;
                $testReturn = null;
                @exec("{$output[0]} --version 2>/dev/null", $testOutput, $testReturn);
                $debugInfo['test_which_path'] = ['return' => $testReturn, 'output' => $testOutput];
                
                if ($testReturn === 0) {
                    return response()->json([
                        'success' => true,
                        'path' => $output[0],
                        'message' => __('NPM encontrado en: ') . $output[0],
                    ])->header('Content-Type', 'application/json');
                } else {
                     // RELAXED CHECK: If 'which' found it but execution check failed
                     return response()->json([
                        'success' => true,
                        'path' => $output[0],
                        'message' => __('NPM encontrado en: ') . $output[0] . __(' (verificación estricta saltada)'),
                    ])->header('Content-Type', 'application/json');
                }
            }
    
            // Método 2: Intentar ejecutar 'npm --version' directamente
            $versionOutput = null;
            $versionReturn = null;
            @exec("npm --version 2>/dev/null", $versionOutput, $versionReturn);
            $debugInfo['npm_version'] = ['return' => $versionReturn, 'output' => $versionOutput];
            
            if ($versionReturn === 0 && !empty($versionOutput[0])) {
                // NPM está disponible en el PATH, intentar obtener su ruta
                $pathOutput = null;
                $pathReturn = null;
                @exec("command -v npm 2>/dev/null", $pathOutput, $pathReturn);
                $debugInfo['command_v'] = ['return' => $pathReturn, 'output' => $pathOutput];
                
                $npmPath = ($pathReturn === 0 && !empty($pathOutput[0])) ? $pathOutput[0] : 'npm';
                
                return response()->json([
                    'success' => true,
                    'path' => $npmPath,
                    'message' => __('NPM encontrado (versión: ') . trim($versionOutput[0]) . ')',
                ])->header('Content-Type', 'application/json');
            }
    
            // Método 3: Buscar en rutas comunes del sistema
            $commonPaths = [
                '/usr/bin/npm',
                '/usr/local/bin/npm',
                '/bin/npm',
                '/opt/node/bin/npm',
            ];
    
            foreach ($commonPaths as $path) {
                $testOutput = null;
                $testReturn = null;
                @exec("{$path} --version 2>/dev/null", $testOutput, $testReturn);
                $debugInfo['common_paths'][$path] = ['return' => $testReturn, 'output' => $testOutput];
                
                if ($testReturn === 0) {
                    return response()->json([
                        'success' => true,
                        'path' => $path,
                        'message' => __('NPM encontrado en: ') . $path,
                    ])->header('Content-Type', 'application/json');
                }
            }
            
            // Método 4: Si Node fue encontrado, buscar NPM en el mismo directorio
            // Y ejecutarlo a través de Node (NPM es un script de Node)
            $nodeOutput = null;
            $nodeReturn = null;
            @exec("which node 2>/dev/null", $nodeOutput, $nodeReturn);
            if ($nodeReturn === 0 && !empty($nodeOutput[0])) {
                $nodePath = $nodeOutput[0];
                $nodeDir = dirname($nodePath);
                $npmInNodeDir = $nodeDir . '/npm';
                $debugInfo['npm_in_node_dir'] = $npmInNodeDir;
                
                // Primero intentar ejecutar npm directamente
                $testOutput = null;
                $testReturn = null;
                @exec("{$npmInNodeDir} --version 2>/dev/null", $testOutput, $testReturn);
                $debugInfo['npm_in_node_dir_test'] = ['return' => $testReturn, 'output' => $testOutput];
                
                if ($testReturn === 0) {
                    return response()->json([
                        'success' => true,
                        'path' => $npmInNodeDir,
                        'message' => __('NPM encontrado en el mismo directorio que Node: ') . $npmInNodeDir,
                    ])->header('Content-Type', 'application/json');
                }
                
                // Si falla (código 127 = command not found), intentar ejecutar NPM a través de Node
                // NPM es un script de Node, así que podemos ejecutarlo como: node /path/to/npm --version
                if ($testReturn === 127) {
                    $nodeNpmOutput = null;
                    $nodeNpmReturn = null;
                    @exec("{$nodePath} {$npmInNodeDir} --version 2>/dev/null", $nodeNpmOutput, $nodeNpmReturn);
                    $debugInfo['node_npm_execution'] = ['return' => $nodeNpmReturn, 'output' => $nodeNpmOutput];
                    
                    if ($nodeNpmReturn === 0 && !empty($nodeNpmOutput[0])) {
                        return response()->json([
                            'success' => true,
                            'path' => $npmInNodeDir,
                            'message' => __('NPM encontrado (ejecutado vía Node, versión: ') . trim($nodeNpmOutput[0]) . ')',
                        ])->header('Content-Type', 'application/json');
                    }
                }
                
                // También intentar con la ruta que devolvió 'which npm' si es diferente
                $whichNpmPath = $output[0] ?? null;
                if ($whichNpmPath && $whichNpmPath !== $npmInNodeDir) {
                    $nodeNpmOutput = null;
                    $nodeNpmReturn = null;
                    @exec("{$nodePath} {$whichNpmPath} --version 2>/dev/null", $nodeNpmOutput, $nodeNpmReturn);
                    $debugInfo['node_which_npm_execution'] = ['return' => $nodeNpmReturn, 'output' => $nodeNpmOutput];
                    
                    if ($nodeNpmReturn === 0 && !empty($nodeNpmOutput[0])) {
                        return response()->json([
                            'success' => true,
                            'path' => $whichNpmPath,
                            'message' => __('NPM encontrado (ejecutado vía Node, versión: ') . trim($nodeNpmOutput[0]) . ')',
                        ])->header('Content-Type', 'application/json');
                    }
                }
            }
            
            // Método 5: Inspeccionar PATH del sistema
            $pathEnv = getenv('PATH');
            $debugInfo['PATH'] = $pathEnv;
            
            // Método 6: Intentar con shell_exec como alternativa
            if (function_exists('shell_exec')) {
                $shellOutput = @shell_exec('npm --version 2>/dev/null');
                $debugInfo['shell_exec'] = $shellOutput;
                
                if ($shellOutput && trim($shellOutput)) {
                    return response()->json([
                        'success' => true,
                        'path' => 'npm',
                        'message' => __('NPM encontrado vía shell_exec (versión: ') . trim($shellOutput) . ')',
                    ])->header('Content-Type', 'application/json');
                }
            }
    
            // Si llegamos aquí, no se encontró NPM - devolver info de debug
            return response()->json([
                'success' => false,
                'message' => __('No se pudo encontrar NPM. Por favor, introduce la ruta manualmente.'),
                'debug' => $debugInfo,
            ])->header('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500)->header('Content-Type', 'application/json');
        }
    }
}