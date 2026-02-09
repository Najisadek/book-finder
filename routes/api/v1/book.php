<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Book\{
    ImportController,
    IndexController,
    SearchController
};

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('/books')->group(function () {
        Route::get('/', IndexController::class);
        Route::get('/search', SearchController::class);
        Route::post('/import', ImportController::class);
    });
});
