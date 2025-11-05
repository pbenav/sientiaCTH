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

// This route renders different views based on authentication
Route::get('/', function () {
    if (auth()->check()) {
        return view('dashboard');
    }
    return view('welcome');
})->name('front');

// Route to Presentation_TFG.svg
Route::get('/pres', function () {
    $pathToFile = public_path('Presentacion_TFG.svg');
    return response()->file($pathToFile);
})->name('pres');

// This route calls a component whose default template is layouts.app.blade.php
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/events', GetTimeRegisters::class)->name('events');
    Route::get('/userstats', StatsComponent::class)->name('stats');
    Route::get('/reports', ReportsComponent::class)->name('reports');
    Route::get('/calendar', \App\Http\Livewire\Calendar::class)->name('calendar');
    Route::get('/messages', \App\Http\Livewire\MessagesComponent::class)->name('messages');

    Route::prefix('users/{user}')->group(function () {
        // Ruta para mostrar todos los metadatos del usuario
        Route::get('/meta', [UserMetaController::class, 'index'])->name('users.meta.index');
        
        // Rutas para el CRUD de metadatos
        Route::post('/meta', [UserMetaController::class, 'store'])->name('users.meta.store');
        Route::delete('/meta/{meta}', [UserMetaController::class, 'destroy'])->name('users.meta.destroy');
    });

    // Debug route for work schedule
    Route::get('/debug-schedule', function() {
        if (!auth()->check()) {
            return 'Not authenticated';
        }
        
        $user = auth()->user();
        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];
        
        $teamTimezone = $user->currentTeam->timezone ?? config('app.timezone');
        $now = \Carbon\Carbon::now($teamTimezone);
        
        return [
            'user' => $user->name,
            'team' => $user->currentTeam->name ?? 'No team',
            'timezone' => $teamTimezone,
            'current_time' => $now->format('Y-m-d H:i:s'),
            'day_of_week' => $now->format('N'),
            'day_letter' => ['L', 'M', 'X', 'J', 'V', 'S', 'D'][$now->format('N') - 1],
            'schedule' => $schedule,
            'has_schedule' => !empty($schedule),
            'workday_event_type' => $user->currentTeam->eventTypes()->where('is_workday_type', true)->first(),
            'clock_service_result' => app(\App\Services\SmartClockInService::class)->getClockAction($user)
        ];
    });

    Route::prefix('fichaje-excepcional')->name('exceptional.clock-in')->group(function () {
        Route::get('/{token}', [App\Http\Controllers\ExceptionalClockInController::class, 'clockIn']);
        Route::get('/formulario/{token}', \App\Http\Livewire\ExceptionalClockIn::class)->name('.form');
    });
});
