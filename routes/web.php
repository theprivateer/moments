<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\MomentController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

// Public timeline
Route::get('/', [MomentController::class, 'index'])->name('moments.index');
Route::get('/moments/{moment}', [MomentController::class, 'show'])->name('moments.show');
Route::get('/feed', FeedController::class)->name('feed');

// Authenticated moment actions
Route::middleware('auth')->group(function () {
    Route::post('/moments', [MomentController::class, 'store'])->name('moments.store');
    Route::get('/moments/{moment}/edit', [MomentController::class, 'edit'])->name('moments.edit');
    Route::patch('/moments/{moment}', [MomentController::class, 'update'])->name('moments.update');
    Route::delete('/moments/{moment}', [MomentController::class, 'destroy'])->name('moments.destroy');
    Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
    Route::post('/tokens', [TokenController::class, 'store'])->name('tokens.store');
    Route::delete('/tokens/{token}', [TokenController::class, 'destroy'])->name('tokens.destroy');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

// Guest-only auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
