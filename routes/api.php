<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\ClinicApiController;
use App\Http\Controllers\Api\QueueApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthApiController::class, 'login']);
    Route::post('/auth/otp', [AuthApiController::class, 'otp']);
    Route::post('/auth/verify', [AuthApiController::class, 'verify']);
    Route::get('/clinics', [ClinicApiController::class, 'index']);
    Route::get('/clinics/{clinic}', [ClinicApiController::class, 'show']);
    Route::get('/queue/status', [QueueApiController::class, 'status']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::post('/auth/logout', [AuthApiController::class, 'logout']);
        Route::get('/tokens', [BookingApiController::class, 'index']);
        Route::post('/tokens', [BookingApiController::class, 'store']);
        Route::post('/tokens/{token}/cancel', [BookingApiController::class, 'cancel']);
    });
});
