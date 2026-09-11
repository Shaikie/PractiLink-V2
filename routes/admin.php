<?php

use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminApplicationWindowController;
use App\Http\Controllers\AdminPlacementController;
use App\Http\Controllers\AdminPlacementLetterController;
use App\Http\Controllers\AdminWorkflowController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:applications.manage')->group(function () {
    Route::resource('application-windows', AdminApplicationWindowController::class)->except(['show'])->parameters(['application-windows'=>'applicationWindow'])->names('application-windows');
});
Route::middleware('permission:applications.review')->group(function () {
    Route::get('applications', [AdminApplicationController::class,'index'])->name('applications.index');
    Route::get('applications/{application}', [AdminApplicationController::class,'show'])->name('applications.show');
    Route::put('applications/{application}/status', [AdminApplicationController::class,'updateStatus'])->name('applications.status.update');
    Route::post('applications/{application}/forward', [AdminApplicationController::class,'forward'])->name('applications.forward');
    Route::post('applications/{application}/return', [AdminApplicationController::class,'returnApplication'])->name('applications.return');
    Route::post('applications/{application}/reject', [AdminApplicationController::class,'reject'])->name('applications.reject');
});
Route::middleware('permission:workflows.manage')->prefix('workflows')->name('workflows.')->group(function () {
    Route::get('/', [AdminWorkflowController::class,'index'])->name('index');
    Route::post('/', [AdminWorkflowController::class,'store'])->name('store');
    Route::get('/{workflow}', [AdminWorkflowController::class,'show'])->name('show');
    Route::post('/{workflow}/versions', [AdminWorkflowController::class,'createVersion'])->name('versions.store');
    Route::put('/versions/{version}', [AdminWorkflowController::class,'updateVersion'])->name('versions.update');
    Route::post('/versions/{version}/publish', [AdminWorkflowController::class,'publish'])->name('versions.publish');
});
Route::middleware('permission:placements.manage')->prefix('placements')->name('placements.')->group(function () {
    Route::get('/', [AdminPlacementController::class,'index'])->name('index');
    Route::get('/applications/{application}/create', [AdminPlacementController::class,'create'])->name('create');
    Route::post('/applications/{application}', [AdminPlacementController::class,'store'])->name('store');
    Route::put('/{placement}/status', [AdminPlacementController::class,'updateStatus'])->name('status.update');
    Route::post('/{placement}/letter', [AdminPlacementLetterController::class,'issue'])->name('letter.issue');
    Route::get('/letters/{letter}', [AdminPlacementLetterController::class,'show'])->name('letter.show');
});
