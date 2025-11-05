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
    Route::get('/calendario', \App\Http\Livewire\Calendar::class)->name('calendar');
    
    // Statistics - User stats dashboard
    Route::get('/estadisticas', StatsComponent::class)->name('stats');
    
    // Reports - Reporting functionality
    Route::get('/informes', ReportsComponent::class)->name('reports');
    
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
});
