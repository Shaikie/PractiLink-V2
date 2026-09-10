<?php

use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminApplicationWindowController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:applications.manage')->group(function () {
    Route::resource('application-windows', AdminApplicationWindowController::class)
        ->except(['show'])
        ->parameters(['application-windows' => 'applicationWindow'])
        ->names('application-windows');
});

Route::middleware('permission:applications.review')->group(function () {
    Route::get('applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::put('applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('applications.status.update');
});
