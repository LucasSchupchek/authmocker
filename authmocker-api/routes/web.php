<?php

use App\Http\Controllers\MockHandlerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'AuthMocker API',
        'timestamp' => now()->toIso8601String(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Mock Handler Routes (Public)
|--------------------------------------------------------------------------
| These routes handle incoming requests to mock servers.
| ANY HTTP method is supported on /mock/{slug}/{path?}
*/

Route::any('/mock/{slug}/{path?}', [MockHandlerController::class, 'handle'])
    ->where('path', '.*');
