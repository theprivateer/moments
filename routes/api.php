<?php

use App\Http\Controllers\Api\MomentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/moments', [MomentController::class, 'store'])->name('api.moments.store');
});
