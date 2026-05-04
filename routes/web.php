<?php

use App\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'dashboard']);
Route::get('/users', [WebController::class, 'users']);
Route::get('/login', [WebController::class, 'login'])->name('login');
Route::get('/trains', [WebController::class, 'trains']);
Route::get('/seats', [WebController::class, 'seats']);
Route::get('/bookings', [WebController::class, 'bookings']);
