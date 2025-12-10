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
    }

    public function generate(): string
    {
        // Force Spanish locale for translations
        app()->setLocale('es');
        
        $html = view('exports.history_pdf', [
            'history' => $this->history,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ])->render();

        // Reuse the Browsershot logic from EventsPdfExport or similar
        // For simplicity, I'll copy the robust logic found in EventsPdfExport
        // In a real refactor, this should be a shared service.

        $chromePath = null;
        $defaultChromePaths = [
            '/home/sientia/.cache/puppeteer/chrome-headless-shell/linux-142.0.7444.175/chrome-headless-shell-linux64/chrome-headless-shell',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/local/bin/google-chrome',
            '/usr/local/bin/chromium',
            getenv('HOME') . '/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome',
            '/home/sientia/.cache/puppeteer/chrome/linux-142.0.7444.175/chrome-linux64/chrome',
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
            throw new \Exception("No se encontró un ejecutable de Chromium válido.");
        }

        $puppeteerArgs = [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--disable-background-networking',
            '--enable-features=NetworkService,NetworkServiceInProcess',
            '--disable-features=site-per-process,IsolateOrigins,SpeculativeServiceWorkerStart',
            '--no-zygote',
        ];

        $nodePath = null;
        $whichOutput = [];
        $whichReturnVar = null;
        exec("which node 2>/dev/null", $whichOutput, $whichReturnVar);
        if ($whichReturnVar === 0 && !empty($whichOutput[0]) && file_exists($whichOutput[0])) {
            $nodePath = $whichOutput[0];
        }

        if (!$nodePath) {
             // Fallback logic similar to EventsPdfExport...
             $commonNodePaths = [
                '/usr/local/bin/node',
                '/usr/bin/node',
                '/bin/node',
             ];
             foreach ($commonNodePaths as $path) {
                if (file_exists($path) && is_executable($path)) {
                    $nodePath = $path;
                    break;
                }
             }
        }

        if (!$nodePath) {
             throw new \Exception("No se encontró Node.js.");
        }

        $npmPath = dirname($nodePath) . '/npm';
        if (!file_exists($npmPath)) {
            // Try which npm
             $whichNpmOutput = [];
             exec("which npm 2>/dev/null", $whichNpmOutput);
             if (!empty($whichNpmOutput[0])) $npmPath = $whichNpmOutput[0];
        }

        $nodeBinDir = dirname($nodePath);

        return Browsershot::html($html)
            ->setNodeBinary($nodePath)
            ->setNpmBinary($npmPath)
            ->setIncludePath('$PATH:' . $nodeBinDir)
            ->setOption('executablePath', $chromePath)
            ->setOption('args', $puppeteerArgs)
            ->format('A4')
            ->landscape()
            ->margins(10, 10, 20, 10)
            ->showBackground()
            ->pdf();
    }
}
