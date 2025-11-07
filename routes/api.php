<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileClockController;
use App\Http\Controllers\Api\ConfigController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    // Public configuration endpoints - no authentication required
    Route::prefix('config')->group(function () {
        Route::get('/server', [ConfigController::class, 'getServerConfig']);
    });
    
    // NFC verification endpoint
    Route::post('/nfc/verify', [ConfigController::class, 'verifyNFCTag']);
    
    // Mobile clock-in API - no authentication required, uses work center code + user secret code
    Route::prefix('mobile')->group(function () {
        Route::post('/clock', [MobileClockController::class, 'clock']);
        Route::get('/status', [MobileClockController::class, 'status']);
        Route::post('/sync', [MobileClockController::class, 'sync']);
    });
});
