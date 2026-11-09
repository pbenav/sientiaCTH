<?php

use App\Http\Controllers\UserMetaController;
use App\Http\Controllers\MessageController;
use App\Livewire\GetTimeRegisters;
use App\Livewire\ReportsComponent;
use App\Livewire\StatsComponent;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/set-locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'es'])) {
        session()->put('locale', $locale);
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
    }
    return back();
})->name('set-locale');

// Legal pages
Route::get('/privacy-policy', [App\Http\Controllers\LegalController::class, 'privacy'])->name('privacy');
Route::get('/terms-of-service', [App\Http\Controllers\LegalController::class, 'terms'])->name('terms');
Route::get('/cookie-policy', [App\Http\Controllers\LegalController::class, 'cookies'])->name('cookies');

// Landing page — shown to all (auth users see a CTA to their dashboard)
Route::get('/', function () {
    return view('welcome');
})->name('front');

Route::get('/about', function () {
    return view('landing');
})->name('landing');

// Route to Presentation_TFG.svg
Route::get('/pres', function () {
    $pathToFile = public_path('Presentacion_TFG.svg');
    return response()->file($pathToFile);
})->name('pres');

// Authenticated routes with logical names
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    // Start menu - Main dashboard with clock-in button
    Route::get('/inicio', function () {
        return view('inicio'); // Previously dashboard.blade.php
    })->name('inicio');
    
    // Ruta para desbloquear Kiosko
    Route::get('/kiosk/unlock', \App\Livewire\KioskUnlockComponent::class)->name('kiosk.unlock');
    
    // Events - Time registration management  
    Route::get('/events', GetTimeRegisters::class)->name('events');
    
    // Calendar - Calendar view
    Route::get('/calendario', function () {
        return view('calendar');
    })->name('calendar');
    
    // Statistics - User stats dashboard
    Route::get('/estadisticas', StatsComponent::class)->name('stats');
    
    // Reports - Reporting functionality
    Route::get('/informes', ReportsComponent::class)->name('reports');
    Route::get('/informes/preview', [App\Http\Controllers\ReportsController::class, 'preview'])->name('reports.preview');
    Route::get('/informes/export', [App\Http\Controllers\ReportsController::class, 'export'])->name('reports.export');
    
    // Messages - Team messages
    Route::get('/mensajes', function () {
        return view('messages');
    })->name('messages');
    Route::get('/mensajes/{id}', [MessageController::class, 'show'])->name('messages.show');

    // Audit Log - Accessible by admins and inspectors (permission check in component)
    Route::get('/audit', \App\Livewire\AuditLogComponent::class)->name('audit.index');

    // Documentation
    // Documentation
    Route::get('/documentacion', [App\Http\Controllers\DocsController::class, 'index'])->name('docs.index');
    Route::get('/documentacion/{locale}/{file}', [App\Http\Controllers\DocsController::class, 'show'])->name('docs.show.locale');
    Route::get('/documentacion/{file}', [App\Http\Controllers\DocsController::class, 'show'])->name('docs.show.root');

    // Announcements - Admin only
    Route::get('/anuncios', function () {
        $user = auth()->user();
        $team = $user->currentTeam;
        
        // Verificar que tiene permiso 'update' (incluye owners y administradores)
        if (!$team || !$user->hasTeamPermission($team, 'update')) {
            abort(403, __('You do not have permission to view team announcements.'));
        }
        
        return view('announcements');
    })->name('announcements');

    Route::prefix('users/{user}')->group(function () {
        // Ruta para mostrar todos los metadatos del usuario
        Route::get('/meta', [UserMetaController::class, 'index'])->name('users.meta.index');
        
        // Rutas para el CRUD de metadatos
        Route::post('/meta', [UserMetaController::class, 'store'])->name('users.meta.store');
        Route::delete('/meta/{meta}', [UserMetaController::class, 'destroy'])->name('users.meta.destroy');
    });

    Route::prefix('fichaje-excepcional')->name('exceptional.clock-in')->group(function () {
        Route::get('/{token}', [App\Http\Controllers\ExceptionalClockInController::class, 'clockIn']);
        Route::get('/formulario/{token}', function ($token) {
            return view('exceptional-clock-in', ['token' => $token]);
        })->name('.form');
    });

    // Team invitation acceptance - Available for team owners and admins
    Route::post('/teams/{team}/invitations/{invitation}/accept', [App\Http\Controllers\TeamInvitationController::class, 'accept'])->name('teams.invitations.accept');

    // Team Preferences
    Route::get('/team/preferences', [App\Http\Controllers\TeamPreferencesController::class, 'index'])->name('team.preferences');
    Route::post('/team/preferences/install', [App\Http\Controllers\TeamPreferencesController::class, 'installDependencies'])->name('team.preferences.install');
    Route::put('/team/preferences/pdf-engine', [App\Http\Controllers\TeamPreferencesController::class, 'updatePdfEngine'])->name('team.preferences.pdf-engine');
    Route::put('/team/preferences/report-limits', [App\Http\Controllers\TeamPreferencesController::class, 'updateReportPreferences'])->name('team.preferences.report-limits');
    Route::post('/team/preferences/detect-chrome', [App\Http\Controllers\TeamPreferencesController::class, 'detectChrome'])->name('team.preferences.detect-chrome');
    Route::post('/team/preferences/detect-node', [App\Http\Controllers\TeamPreferencesController::class, 'detectNode'])->name('team.preferences.detect-node');
    Route::post('/team/preferences/detect-npm', [App\Http\Controllers\TeamPreferencesController::class, 'detectNpm'])->name('team.preferences.detect-npm');

    // Dashboard Preferences
    Route::post('/dashboard/preferences', [App\Http\Controllers\DashboardPreferencesController::class, 'store'])->name('dashboard.preferences.store');
    Route::delete('/dashboard/preferences', [App\Http\Controllers\DashboardPreferencesController::class, 'reset'])->name('dashboard.preferences.reset');

    // Impersonation - Solo admin global
    Route::post('/impersonate/{user}', [App\Http\Controllers\ImpersonateController::class, 'impersonate'])->name('impersonate');
    Route::post('/impersonate/leave', [App\Http\Controllers\ImpersonateController::class, 'leave'])->name('impersonate.leave');
});

// Admin routes - Accessible to global administrators and team administrators
Route::middleware(['auth', 'verified', 'admin_or_team_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', \App\Livewire\Admin\AdminDashboardComponent::class)->name('dashboard');

    // User Management (Scoped for team admins, global for is_admin)
    Route::get('/users', \App\Livewire\Admin\UserManagementComponent::class)->name('users.index');

    // Global Admin Only Routes
    Route::middleware(['is_admin'])->group(function () {
        // Mail Settings
        Route::get('/mail-settings', function () {
            return view('admin.mail-settings');
        })->name('mail-settings');

        // Global App Settings
        Route::get('/settings', function () {
            return view('admin.app-settings');
        })->name('settings');

        // SBOM Download
        Route::get('/sbom', function () {
            $path = base_path('sbom.json');
            if (!file_exists($path)) {
                abort(404, 'SBOM not generated yet.');
            }
            return response()->download($path, 'sbom.json', [
                'Content-Type' => 'application/json',
            ]);
        })->name('sbom.download');

        // Team Administration
        Route::get('/teams', [App\Http\Controllers\Admin\TeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/{team}/edit', [App\Http\Controllers\Admin\TeamController::class, 'edit'])->name('teams.edit');
        Route::put('/teams/{team}', [App\Http\Controllers\Admin\TeamController::class, 'update'])->name('teams.update');
        Route::delete('/teams/{team}', [App\Http\Controllers\Admin\TeamController::class, 'destroy'])->name('teams.destroy');
        Route::post('/teams/{team}/members', [App\Http\Controllers\Admin\TeamController::class, 'addMember'])->name('teams.add-member');
        Route::put('/teams/{team}/members/{user}/role', [App\Http\Controllers\Admin\TeamController::class, 'updateMemberRole'])->name('teams.update-member-role');
        Route::delete('/teams/{team}/members/{user}', [App\Http\Controllers\Admin\TeamController::class, 'removeMember'])->name('teams.remove-member');
        Route::post('/teams/{team}/members/{user}/transfer', [App\Http\Controllers\Admin\TeamController::class, 'transferUser'])->name('teams.transfer-user');
        Route::put('/teams/{team}/transfer-ownership', [App\Http\Controllers\Admin\TeamController::class, 'transferOwnership'])->name('teams.transfer-ownership');
        Route::put('/teams/{team}/assign-owner', [App\Http\Controllers\Admin\TeamController::class, 'assignOwner'])->name('teams.assign-owner');
        Route::post('/teams/{team}/invitations/{invitation}/accept', [App\Http\Controllers\TeamInvitationController::class, 'accept'])->name('teams.accept-invitation');
    });
});

/*
|--------------------------------------------------------------------------
| Mobile Web Routes (for Flutter WebView)
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')->name('mobile.')->group(function () {
    // Catch-all route for Flutter Web app
    Route::get('{any?}', function () {
        $path = public_path('mobile/index.html');
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return response('Mobile Web App is being prepared. Please check back later.', 503);
    })->where('any', '.*')->name('home');
});
