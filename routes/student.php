<?php

use App\Http\Controllers\StudentApplicationController;
use App\Http\Controllers\StudentApplicationDocumentController;
use App\Http\Controllers\StudentApplicationProgressController;
use App\Http\Controllers\StudentPlacementController;
use App\Http\Controllers\StudentProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/student/profile', [StudentProfileController::class,'edit'])->name('student.profile.edit');
Route::put('/student/profile', [StudentProfileController::class,'update'])->name('student.profile.update');
Route::put('/student/profile/password', [StudentProfileController::class,'updatePassword'])->name('student.profile.password.update');
Route::prefix('student/applications')->name('student.applications.')->group(function () {
    Route::get('/', [StudentApplicationController::class,'index'])->name('index');
    Route::get('/create', [StudentApplicationController::class,'create'])->name('create');
    Route::post('/', [StudentApplicationController::class,'store'])->name('store');
    Route::get('/{application}', [StudentApplicationController::class,'show'])->name('show');
    Route::get('/{application}/progress', [StudentApplicationProgressController::class,'show'])->name('progress');
    Route::put('/{application}', [StudentApplicationController::class,'update'])->name('update');
    Route::post('/{application}/submit', [StudentApplicationController::class,'submit'])->name('submit');
    Route::post('/{application}/cancel', [StudentApplicationController::class,'cancel'])->name('cancel');
    Route::post('/{application}/documents', [StudentApplicationDocumentController::class,'store'])->name('documents.store');
    Route::get('/{application}/documents/{document}', [StudentApplicationDocumentController::class,'show'])->name('documents.show');
    Route::get('/{application}/documents/{document}/preview', [StudentApplicationDocumentController::class,'preview'])->name('documents.preview');
    Route::get('/{application}/documents/{document}/download', [StudentApplicationDocumentController::class,'download'])->name('documents.download');
    Route::delete('/{application}/documents/{document}', [StudentApplicationDocumentController::class,'destroy'])->name('documents.destroy');
});
Route::get('/student/placements/letters/{letter}', [StudentPlacementController::class,'letter'])->name('student.placements.letter');
