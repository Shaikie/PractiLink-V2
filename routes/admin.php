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

Route::middleware('permission:applications.manage')->group(function(){Route::resource('application-windows',AdminApplicationWindowController::class)->except(['show'])->parameters(['application-windows'=>'applicationWindow'])->names('application-windows');});
Route::middleware('permission:applications.view')->group(function(){Route::get('applications',[AdminApplicationController::class,'index'])->name('applications.index');Route::get('applications/{application}',[AdminApplicationController::class,'show'])->name('applications.show');Route::get('applications/{application}/documents/{document}/preview',[StudentApplicationDocumentController::class,'preview'])->name('applications.documents.preview');Route::get('applications/{application}/documents/{document}/download',[StudentApplicationDocumentController::class,'download'])->name('applications.documents.download');});
Route::middleware('permission:applications.forward')->post('applications/{application}/forward',[AdminApplicationController::class,'forward'])->name('applications.forward');
Route::middleware('permission:applications.return')->post('applications/{application}/return',[AdminApplicationController::class,'returnApplication'])->name('applications.return');
Route::middleware('permission:applications.reject')->post('applications/{application}/reject',[AdminApplicationController::class,'reject'])->name('applications.reject');
Route::middleware('permission:workflows.manage')->prefix('workflows')->name('workflows.')->group(function(){Route::get('/',[AdminWorkflowController::class,'index'])->name('index');Route::post('/',[AdminWorkflowController::class,'store'])->name('store');Route::get('/{workflow}',[AdminWorkflowController::class,'show'])->name('show');Route::post('/{workflow}/versions',[AdminWorkflowController::class,'createVersion'])->name('versions.store');Route::put('/versions/{version}',[AdminWorkflowController::class,'updateVersion'])->name('versions.update');Route::post('/versions/{version}/publish',[AdminWorkflowController::class,'publish'])->name('versions.publish');});
Route::middleware('permission:staff.manage')->prefix('staff')->name('staff.')->group(function(){Route::get('/',[AdminStaffController::class,'index'])->name('index');Route::get('/create',[AdminStaffController::class,'create'])->name('create');Route::post('/',[AdminStaffController::class,'store'])->name('store');Route::get('/{staff}/edit',[AdminStaffController::class,'edit'])->name('edit');Route::put('/{staff}',[AdminStaffController::class,'update'])->name('update');});
Route::middleware('permission:roles.manage')->prefix('roles')->name('roles.')->group(function(){Route::get('/',[AdminRoleController::class,'index'])->name('index');Route::post('/',[AdminRoleController::class,'store'])->name('store');Route::put('/{role}',[AdminRoleController::class,'update'])->name('update');});
Route::middleware('permission:placements.manage')->prefix('placements')->name('placements.')->group(function(){Route::get('/',[AdminPlacementController::class,'index'])->name('index');Route::get('/applications/{application}/create',[AdminPlacementController::class,'create'])->middleware('permission:applications.assign_supervisor')->name('create');Route::post('/applications/{application}',[AdminPlacementController::class,'store'])->middleware(['permission:applications.assign_supervisor','permission:placements.create'])->name('store');Route::put('/{placement}/status',[AdminPlacementController::class,'updateStatus'])->name('status.update');Route::post('/{placement}/letter',[AdminPlacementLetterController::class,'issue'])->name('letter.issue');Route::get('/letters/{letter}',[AdminPlacementLetterController::class,'show'])->name('letter.show');});
