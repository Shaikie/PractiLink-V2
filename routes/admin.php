<?php

use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminApplicationWindowController;
use App\Http\Controllers\AdminPlacementController;
use App\Http\Controllers\AdminPlacementLetterController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminWorkflowController;
use App\Http\Controllers\StudentApplicationDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:applications.manage')->group(function () {
    Route::resource('application-windows', AdminApplicationWindowController::class)
        ->except(['show'])
        ->parameters(['application-windows' => 'applicationWindow'])
        ->names('application-windows');
});

Route::middleware('permission:applications.view')->group(function () {
    Route::get('applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::get('applications/{application}/documents/{document}/preview', [StudentApplicationDocumentController::class, 'preview'])->name('applications.documents.preview');
    Route::get('applications/{application}/documents/{document}/download', [StudentApplicationDocumentController::class, 'download'])->name('applications.documents.download');
    Route::post('applications/{application}/action', [AdminApplicationController::class, 'action'])->name('applications.action');
});

Route::middleware('permission:workflows.manage')->prefix('workflows')->name('workflows.')->group(function () {
    Route::get('/', [AdminWorkflowController::class, 'index'])->name('index');
    Route::get('/create', [AdminWorkflowController::class, 'create'])->name('create');
    Route::post('/', [AdminWorkflowController::class, 'store'])->name('store');
    Route::get('/{workflow}', [AdminWorkflowController::class, 'show'])->name('show');
    Route::post('/{workflow}/versions', [AdminWorkflowController::class, 'createVersion'])->name('versions.store');
    Route::put('/versions/{version}', [AdminWorkflowController::class, 'updateVersion'])->name('versions.update');
    Route::post('/versions/{version}/publish', [AdminWorkflowController::class, 'publish'])->name('versions.publish');
});

Route::middleware('permission:users.manage')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', [AdminStaffController::class, 'index'])->name('index');
    Route::get('/create', [AdminStaffController::class, 'create'])->name('create');
    Route::post('/', [AdminStaffController::class, 'store'])->name('store');
    Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [AdminRoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
    Route::get('/{staff}/edit', [AdminStaffController::class, 'edit'])->name('edit');
    Route::put('/{staff}', [AdminStaffController::class, 'update'])->name('update');
});

Route::middleware('permission:placements.manage')->prefix('placements')->name('placements.')->group(function () {
    Route::get('/', [AdminPlacementController::class, 'index'])->name('index');
    Route::get('/applications/{application}/create', [AdminPlacementController::class, 'create'])->name('create');
    Route::post('/applications/{application}', [AdminPlacementController::class, 'store'])->name('store');
    Route::put('/{placement}/status', [AdminPlacementController::class, 'updateStatus'])->name('status.update');
    Route::post('/{placement}/letter', [AdminPlacementLetterController::class, 'issue'])->name('letter.issue');
    Route::get('/letters/{letter}', [AdminPlacementLetterController::class, 'show'])->name('letter.show');
});
