<?php

use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\MomentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/images', [ImageController::class, 'store'])->name('api.v1.images.store');
        Route::post('/moments', [MomentController::class, 'store'])->name('api.v1.moments.store');
    });
});
