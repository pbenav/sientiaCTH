<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileClockController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\ScheduleController;
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

    // Clean API endpoints (Aliases for mobile app without /mobile prefix)
    Route::post('/clock', [MobileClockController::class, 'clock']);
    Route::post('/status', [MobileClockController::class, 'status']);
    Route::post('/team/switch', [MobileClockController::class, 'confirmTeamSwitch']); // Renamed for clarity
    Route::post('/sync', [MobileClockController::class, 'sync']);
    
    // History
    Route::post('/history', [HistoryController::class, 'index']);
    
    // Schedule
    Route::post('/schedule', [ScheduleController::class, 'index']);
    Route::post('/schedule/update', [ScheduleController::class, 'update']);
    
    // Profile
    Route::post('/profile/update', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
    
    // Worker Data
    Route::get('/worker/{code}', [MobileClockController::class, 'getWorkerData']);

    // Legacy Mobile API endpoints (Kept for backward compatibility)
    Route::prefix('mobile')->group(function () {
        // ... mapped to clean routes eventually
        Route::post('/clock', [MobileClockController::class, 'clock']);
        Route::post('/status', [MobileClockController::class, 'status']);
        Route::post('/confirm-team-switch', [MobileClockController::class, 'confirmTeamSwitch']);
        Route::post('/sync', [MobileClockController::class, 'sync']);
        // History
        Route::post('/history', [HistoryController::class, 'index']);
        
        // Schedule
        Route::post('/schedule', [ScheduleController::class, 'index']);
        Route::post('/schedule/update', [ScheduleController::class, 'update']);
        
        // Profile
        Route::post('/profile/update', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
        
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
