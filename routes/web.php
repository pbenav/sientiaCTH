<?php

use App\Http\Controllers\UserMetaController;
use App\Http\Livewire\GetTimeRegisters;
use App\Http\Livewire\ReportsComponent;
use App\Http\Livewire\StatsComponent;
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

// Landing page - NumPad for unauthenticated users
Route::get('/', function () {
    // Unauthenticated users see NumPad, authenticated users go to Start menu
    if (auth()->check()) {
        return redirect()->route('inicio');
    }
    return view('welcome'); // NumPad only
})->name('front');

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
    
    // Messages - Team messages
    Route::get('/mensajes', \App\Http\Livewire\MessagesComponent::class)->name('messages');

    Route::prefix('users/{user}')->group(function () {
        // Ruta para mostrar todos los metadatos del usuario
        Route::get('/meta', [UserMetaController::class, 'index'])->name('users.meta.index');
        
        // Rutas para el CRUD de metadatos
        Route::post('/meta', [UserMetaController::class, 'store'])->name('users.meta.store');
        Route::delete('/meta/{meta}', [UserMetaController::class, 'destroy'])->name('users.meta.destroy');
    });

    Route::prefix('fichaje-excepcional')->name('exceptional.clock-in')->group(function () {
        Route::get('/{token}', [App\Http\Controllers\ExceptionalClockInController::class, 'clockIn']);
        Route::get('/formulario/{token}', \App\Http\Livewire\ExceptionalClockIn::class)->name('.form');
    });

    // Team Preferences
    Route::get('/team/preferences', [App\Http\Controllers\TeamPreferencesController::class, 'index'])->name('team.preferences');
    Route::post('/team/preferences/install', [App\Http\Controllers\TeamPreferencesController::class, 'installDependencies'])->name('team.preferences.install');
});

/*
|--------------------------------------------------------------------------
| Mobile Web Routes (for Flutter WebView)
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')->name('mobile.')->group(function () {
    // Authentication routes (no middleware)
    Route::get('/auth', [App\Http\Controllers\Mobile\MobileWebController::class, 'showAuth'])->name('auth');
    Route::post('/auth/login', [App\Http\Controllers\Mobile\MobileWebController::class, 'login'])->name('auth.login');
    
    // Protected mobile routes
    Route::middleware(['mobile.auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\Mobile\MobileWebController::class, 'home'])->name('home');
        Route::get('/home', [App\Http\Controllers\Mobile\MobileWebController::class, 'home'])->name('home.alt');
        Route::get('/history', [App\Http\Controllers\Mobile\MobileWebController::class, 'history'])->name('history');
        Route::get('/schedule', [App\Http\Controllers\Mobile\MobileWebController::class, 'schedule'])->name('schedule');
        Route::get('/profile', [App\Http\Controllers\Mobile\MobileWebController::class, 'profile'])->name('profile');
        Route::get('/reports', [App\Http\Controllers\Mobile\MobileWebController::class, 'reports'])->name('reports');
        Route::post('/logout', [App\Http\Controllers\Mobile\MobileWebController::class, 'logout'])->name('logout');
    });

});
