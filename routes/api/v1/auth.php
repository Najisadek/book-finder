<?php

use App\Http\Controllers\Api\V1\Auth\{RegisterController, LoginController, LogoutController};
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class)->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', LogoutController::class);
});