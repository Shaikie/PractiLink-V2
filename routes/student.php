<?php

use App\Http\Controllers\StudentApplicationController;
use App\Http\Controllers\StudentProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
Route::put('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');
Route::put('/student/profile/password', [StudentProfileController::class, 'updatePassword'])->name('student.profile.password.update');

Route::prefix('student/applications')->name('student.applications.')->group(function () {
    Route::get('/', [StudentApplicationController::class, 'index'])->name('index');
    Route::post('/', [StudentApplicationController::class, 'store'])->name('store');
    Route::get('/{application}', [StudentApplicationController::class, 'show'])->name('show');
    Route::post('/{application}/submit', [StudentApplicationController::class, 'submit'])->name('submit');
    Route::post('/{application}/cancel', [StudentApplicationController::class, 'cancel'])->name('cancel');
});
