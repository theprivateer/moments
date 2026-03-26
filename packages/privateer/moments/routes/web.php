<?php

use Illuminate\Support\Facades\Route;
use Privateer\Moments\Http\Controllers\AccountController;
use Privateer\Moments\Http\Controllers\AtomFeedController;
use Privateer\Moments\Http\Controllers\Auth\LoginController;
use Privateer\Moments\Http\Controllers\FeedController;
use Privateer\Moments\Http\Controllers\GlideController;
use Privateer\Moments\Http\Controllers\JsonFeedController;
use Privateer\Moments\Http\Controllers\MomentController;
use Privateer\Moments\Http\Controllers\TokenController;

Route::get('/img/{path}', GlideController::class)->where('path', '.*')->name('glide');

Route::get('/', [MomentController::class, 'index'])->name('moments.index');
Route::get('/moments/{moment}', [MomentController::class, 'show'])->name('moments.show');
Route::get('/feed', FeedController::class)->name('feed');
Route::get('/feed/atom', AtomFeedController::class)->name('feed.atom');
Route::get('/feed/json', JsonFeedController::class)->name('feed.json');

Route::middleware(config('moments.authenticated_middleware', ['auth']))->group(function (): void {
    Route::post('/moments', [MomentController::class, 'store'])->name('moments.store');
    Route::get('/moments/{moment}/edit', [MomentController::class, 'edit'])->name('moments.edit');
    Route::patch('/moments/{moment}', [MomentController::class, 'update'])->name('moments.update');
    Route::delete('/moments/{moment}', [MomentController::class, 'destroy'])->name('moments.destroy');
    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::patch('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::get('/tokens', function () {
        return redirect()->route('account.show');
    })->name('tokens.index');
    Route::post('/tokens', [TokenController::class, 'store'])->name('tokens.store');
    Route::delete('/tokens/{token}', [TokenController::class, 'destroy'])->name('tokens.destroy');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware(config('moments.guest_middleware', ['guest']))->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
