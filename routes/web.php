<?php

use App\Http\Livewire\GetTimeRegisters;
use App\Http\Livewire\LeaveManager;
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

// This route renders a view without the need of a controller
Route::get('/', function () {
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
});




