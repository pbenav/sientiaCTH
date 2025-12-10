<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DetectBrowsershotDependencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'browsershot:detect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect Chrome and Node.js paths for Browsershot PDF generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Detecting Browsershot dependencies...');
        $this->newLine();

        // Detect Chrome/Chromium
        $this->info('📦 Chrome/Chromium:');
        $chromePath = $this->detectChrome();
        if ($chromePath) {
            $this->info("   ✅ Found: {$chromePath}");
        } else {
            $this->error('   ❌ Not found');
        }
        $this->newLine();

        // Detect Node.js
        $this->info('📦 Node.js:');
        $nodePath = $this->detectNode();
        if ($nodePath) {
            $this->info("   ✅ Found: {$nodePath}");
            
            // Get Node.js version
            exec("$nodePath --version 2>/dev/null", $nodeVersion);
            if (!empty($nodeVersion[0])) {
                $this->info("   📌 Version: {$nodeVersion[0]}");
            }
        } else {
            $this->error('   ❌ Not found');
            $this->newLine();
            $this->warn('💡 Install Node.js:');
            $this->line('   - Ubuntu/Debian: apt-get install nodejs');
            $this->line('   - CentOS/RedHat: yum install nodejs');
            $this->line('   - Or use NVM: https://github.com/nvm-sh/nvm');
        }
        $this->newLine();

        // Detect NPM
        $this->info('📦 NPM:');
        $npmPath = $this->detectNpm($nodePath);
        if ($npmPath) {
            $this->info("   ✅ Found: {$npmPath}");
            
            // Get NPM version
            exec("$npmPath --version 2>/dev/null", $npmVersion);
            if (!empty($npmVersion[0])) {
                $this->info("   📌 Version: {$npmVersion[0]}");
            }
        } else {
            $this->error('   ❌ Not found');
        }
        $this->newLine();

        // Summary
        if ($chromePath && $nodePath && $npmPath) {
            $this->info('✅ All dependencies detected successfully!');
            $this->info('🎉 PDF generation with Browsershot should work correctly.');
        } else {
            $this->error('⚠️  Some dependencies are missing. Please install them to use Browsershot.');
        }

        return 0;
    }

    private function detectChrome()
    {
        // Try 'which' first
        exec("which google-chrome 2>/dev/null || which chromium 2>/dev/null || which chromium-browser 2>/dev/null", $which);
        if (!empty($which[0]) && file_exists($which[0])) {
            return $which[0];
        }

        $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '/root');
        
        $paths = [
            '/home/sientia/.cache/puppeteer/chrome-headless-shell/linux-142.0.7444.175/chrome-headless-shell-linux64/chrome-headless-shell',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/local/bin/google-chrome',
            '/usr/local/bin/chromium',
            $homeDir . '/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome',
        ];

        foreach ($paths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function detectNode()
    {
        // Try 'which' first
        exec("which node 2>/dev/null", $which);
        if (!empty($which[0]) && file_exists($which[0])) {
            return $which[0];
        }

        $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '/root');
        
        $paths = [
            '/usr/local/bin/node',
            '/usr/bin/node',
            '/bin/node',
            '/opt/node/bin/node',
        ];

        // Check NVM installations
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
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function detectNpm($nodePath)
    {
        if (!$nodePath) {
            return null;
        }

        // Try same directory as node
        $npmPath = dirname($nodePath) . '/npm';
        if (file_exists($npmPath)) {
            return $npmPath;
        }

        // Try 'which'
        exec("which npm 2>/dev/null", $which);
        if (!empty($which[0]) && file_exists($which[0])) {
            return $which[0];
        }

        $paths = [
            '/usr/local/bin/npm',
            '/usr/bin/npm',
            '/bin/npm',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
