<?php

use Illuminate\Support\Facades\Route;
use Privateer\Moments\Http\Controllers\Api\ImageController;
use Privateer\Moments\Http\Controllers\Api\MomentController;

Route::prefix('v1')->group(function (): void {
    Route::get('/moments', [MomentController::class, 'index'])
        ->middleware('abilities:moments:read')
        ->name('api.v1.moments.index');

    Route::middleware('abilities:moments:write')->group(function (): void {
        Route::post('/images', [ImageController::class, 'store'])->name('api.v1.images.store');
        Route::post('/moments', [MomentController::class, 'store'])->name('api.v1.moments.store');
        Route::patch('/moments/{moment}', [MomentController::class, 'update'])->name('api.v1.moments.update');
        Route::delete('/moments/{moment}', [MomentController::class, 'destroy'])->name('api.v1.moments.destroy');
    });
});
