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


Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    // Login for mobile app
    Route::post('login', [\App\Http\Controllers\Api\LoginController::class, 'login']);

    // Public configuration endpoints - no authentication required
    Route::prefix('config')->group(function () {
        Route::get('/server', [ConfigController::class, 'getServerConfig']);
        Route::get('/ping', [ConfigController::class, 'ping']);
        Route::get('/work-centers/nfc', [ConfigController::class, 'getWorkCentersWithNFC']);
    });

    // Ruta unificada para verificación NFC
    Route::post('nfc/verify', [ConfigController::class, 'verifyNFCTag']);

    // Mobile API endpoints
    Route::prefix('mobile')->group(function () {
        Route::post('/clock', [MobileClockController::class, 'clock']);
        Route::post('/status', [MobileClockController::class, 'status']);
        Route::post('/sync', [MobileClockController::class, 'sync']);
        Route::post('/history', [App\Http\Controllers\Api\HistoryController::class, 'index']);
        // Worker data endpoint used by mobile setup (moved here to keep all mobile endpoints under /api/v1/mobile)
        Route::get('/worker/{code}', [MobileClockController::class, 'getWorkerData']);
    });

    // Authenticated API routes for mobile app
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
        Route::post('work_center/validate', [\App\Http\Controllers\Api\WorkCenterAPIController::class, 'validateCode']);
        Route::post('punch', [\App\Http\Controllers\Api\PunchController::class, 'store']);
        Route::get('admin/work_centers', [\App\Http\Controllers\Api\WorkCenterAPIController::class, 'index'])->middleware('isTeamAdmin');
    });
});

    // NOTE: worker endpoint moved into /v1/mobile group above to avoid duplicate/ambiguous endpoints
