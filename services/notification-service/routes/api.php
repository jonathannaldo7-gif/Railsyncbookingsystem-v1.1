<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::get('/health', [HealthController::class, 'check']);
Route::get('/status', function () {
    return response()->json([
        'service' => 'notification-service',
        'status' => 'running',
        'timestamp' => now(),
    ]);
});
