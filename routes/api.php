<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileClockController;

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
    Route::post('login', [\App\Http\Controllers\Api\LoginController::class, 'login']);
    
    // Mobile clock-in API - no authentication required, uses work center code + user secret code
    // Mobile API routes
Route::prefix('v1/mobile')->group(function () {
    Route::post('/clock', [MobileClockController::class, 'clock']);
    Route::get('/status', [MobileClockController::class, 'status']);
    Route::post('/sync', [MobileClockController::class, 'sync']);
});

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
        Route::post('work_center/validate', [\App\Http\Controllers\Api\WorkCenterAPIController::class, 'validateCode']);
        Route::post('punch', [\App\Http\Controllers\Api\PunchController::class, 'store']);
        Route::get('admin/work_centers', [\App\Http\Controllers\Api\WorkCenterAPIController::class, 'index'])->middleware('isTeamAdmin');
    });
});
