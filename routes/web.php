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

    // Debug route for clock issues
    Route::get('/debug-clock', function() {
        if (!auth()->check()) {
            return response()->json(['error' => 'Not authenticated']);
        }
        
        $user = auth()->user();
        $clockService = app(\App\Services\SmartClockInService::class);
        
        // Get user metadata
        $scheduleMeta = $user->meta->where('meta_key', 'work_schedule')->first();
        $schedule = $scheduleMeta ? json_decode($scheduleMeta->meta_value, true) : [];
        
        // Get team info
        $team = $user->currentTeam;
        $teamTimezone = $team ? ($team->timezone ?? config('app.timezone')) : config('app.timezone');
        $now = \Carbon\Carbon::now($teamTimezone);
        
        // Get workday event type
        $workdayEventType = $team ? $team->eventTypes()->where('is_workday_type', true)->first() : null;
        
        // Get clock action result
        $clockResult = $clockService->getClockAction($user);
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'current_team_id' => $user->current_team_id,
            ],
            'team' => $team ? [
                'id' => $team->id,
                'name' => $team->name,
                'timezone' => $team->timezone,
            ] : null,
            'timezone' => $teamTimezone,
            'current_time' => $now->format('Y-m-d H:i:s'),
            'day_of_week' => $now->format('N'),
            'day_letter' => ['L', 'M', 'X', 'J', 'V', 'S', 'D'][$now->format('N') - 1],
            'schedule' => [
                'exists' => !empty($schedule),
                'raw' => $schedule,
                'meta_id' => $scheduleMeta ? $scheduleMeta->id : null,
            ],
            'workday_event_type' => $workdayEventType ? [
                'id' => $workdayEventType->id,
                'name' => $workdayEventType->name,
                'is_workday_type' => $workdayEventType->is_workday_type,
            ] : null,
            'clock_service_result' => $clockResult,
            'open_events' => $user->events()->where('is_open', true)->whereNull('end')->get()->map(function($event) {
                return [
                    'id' => $event->id,
                    'start' => $event->start,
                    'event_type_id' => $event->event_type_id,
                    'is_open' => $event->is_open,
                ];
            }),
        ], JSON_PRETTY_PRINT);
    });

    Route::prefix('fichaje-excepcional')->name('exceptional.clock-in')->group(function () {
        Route::get('/{token}', [App\Http\Controllers\ExceptionalClockInController::class, 'clockIn']);
        Route::get('/formulario/{token}', \App\Http\Livewire\ExceptionalClockIn::class)->name('.form');
    });
});
