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
     */
    public function installDependencies()
    {
        $output = [];
        $returnVar = 0;

        // Execute the Puppeteer installation script
        exec('php initialize_puppeteer.php', $output, $returnVar);

        return response()->json([
            'log' => implode("\n", $output),
            'success' => $returnVar === 0,
        ]);
    }
}