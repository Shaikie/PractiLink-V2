<?php

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\StudentAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest.any')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login']);
    Route::get('/register', [StudentAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [StudentAuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'createLink'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'createResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [StudentAuthController::class, 'logout'])
    ->middleware('auth.any')
    ->name('logout');
