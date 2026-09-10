<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/student/profile', [ProfileController::class, 'editStudent'])->name('student.profile.edit');
    Route::put('/student/profile', [ProfileController::class, 'updateStudent'])->name('student.profile.update');
});
