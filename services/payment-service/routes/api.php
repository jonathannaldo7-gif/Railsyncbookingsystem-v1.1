<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::get('/health', [HealthController::class, 'check']);
Route::get('/status', function () {
    return response()->json([
        'service' => 'payment-service',
        'status' => 'running',
        'timestamp' => now(),
    ]);
});
