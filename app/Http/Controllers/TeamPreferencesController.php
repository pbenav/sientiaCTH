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
     * Execute the Puppeteer installation script.
     */
    public function installDependencies()
    {
        $output = [];
        $returnVar = 0;

        // Execute the Puppeteer installation script
        exec('php initialize_puppeteer.php', $output, $returnVar);

        if ($returnVar !== 0) {
            return redirect()->back()->with('error', 'Error al instalar Puppeteer: ' . implode("\n", $output));
        }

        return redirect()->back()->with('success', 'Puppeteer instalado correctamente.');
    }
}