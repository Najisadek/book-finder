<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Favorite\{
    IndexController,
    StoreController,
    DestroyController
};

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('/favorites')->group(function () {
        Route::get('/', IndexController::class);
        Route::post('/store/{book}', StoreController::class);
        Route::delete('/destroy/{book}', DestroyController::class);
    });
});