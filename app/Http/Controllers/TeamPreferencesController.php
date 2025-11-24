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
    /**
     * Update the team's PDF engine preference.
     */
    public function updatePdfEngine(Request $request)
    {
        $request->validate([
            'pdf_engine' => 'required|in:browsershot,mpdf',
            'chrome_path' => 'nullable|string',
        ]);

        $team = $request->user()->currentTeam;
        
        $team->forceFill([
            'pdf_engine' => $request->pdf_engine,
            'chrome_path' => $request->chrome_path,
        ])->save();

        return back()->with('success', __('Preferencias de PDF actualizadas correctamente.'));
    }
}