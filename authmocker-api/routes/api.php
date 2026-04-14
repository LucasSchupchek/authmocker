<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthTypeController;
use App\Http\Controllers\MockCredentialController;
use App\Http\Controllers\MockEndpointController;
use App\Http\Controllers\MockHandlerController;
use App\Http\Controllers\MockServerController;
use App\Http\Controllers\RequestLogController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/auth-types', [AuthTypeController::class, 'index']);

// Auth (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Auth (protected)
Route::prefix('auth')->middleware(AuthMiddleware::class)->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(AuthMiddleware::class)->group(function () {
    // Mock Servers
    Route::apiResource('servers', MockServerController::class);

    // Mock Endpoints
    Route::get('servers/{server}/endpoints', [MockEndpointController::class, 'index']);
    Route::post('servers/{server}/endpoints', [MockEndpointController::class, 'store']);
    Route::get('endpoints/{endpoint}', [MockEndpointController::class, 'show']);
    Route::put('endpoints/{endpoint}', [MockEndpointController::class, 'update']);
    Route::delete('endpoints/{endpoint}', [MockEndpointController::class, 'destroy']);

    // Mock Credentials
    Route::get('servers/{server}/credentials', [MockCredentialController::class, 'index']);
    Route::post('servers/{server}/credentials', [MockCredentialController::class, 'store']);
    Route::get('credentials/{credential}', [MockCredentialController::class, 'show']);
    Route::put('credentials/{credential}', [MockCredentialController::class, 'update']);
    Route::delete('credentials/{credential}', [MockCredentialController::class, 'destroy']);

    // Request Logs
    Route::get('servers/{server}/logs', [RequestLogController::class, 'index']);
    Route::delete('servers/{server}/logs', [RequestLogController::class, 'destroy']);
});
