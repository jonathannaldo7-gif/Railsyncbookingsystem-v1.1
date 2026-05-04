<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\TrainController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('users', UserController::class);
Route::post('users/login', [UserController::class, 'login']);

Route::apiResource('trains', TrainController::class);

Route::apiResource('seats', SeatController::class);
Route::post('seats/{id}/reserve', [SeatController::class, 'reserve']);
Route::post('seats/{id}/release', [SeatController::class, 'release']);

Route::apiResource('bookings', BookingController::class);
Route::post('bookings/{id}/confirm', [BookingController::class, 'confirm']);
