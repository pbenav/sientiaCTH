<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\GetTimeRegisters;
use App\Http\Livewire\StatsComponent;
use App\Http\Livewire\ReportsComponent;
use App\Http\Controllers\UserController;

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
// Route for location
Route::get('userloc', [UserController::class, 'index']);

// This route renders a view without the need of a controller
Route::get('/', function () {
    return view('welcome');
})->name('front');

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

