<?php

use App\Http\Controllers\StudentAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login']);

    Route::get('/register', [StudentAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [StudentAuthController::class, 'register']);
});

Route::post('/logout', [StudentAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
