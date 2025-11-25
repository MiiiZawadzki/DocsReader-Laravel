<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentController;
use Modules\Document\Http\Controllers\FileController;
use Modules\Document\Http\Controllers\ManageDocumentController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('documents', DocumentController::class);
    Route::post('document-reads/{document}', [DocumentController::class, 'markAsRead']);

    Route::get('/manage/documents', [ManageDocumentController::class, 'index']);
    Route::get('/manage/documents/{document}', [ManageDocumentController::class, 'show']);
    Route::delete('/manage/documents/{document}/delete', [ManageDocumentController::class, 'delete']);
    Route::get('/manage/documents/{document}/users', [ManageDocumentController::class, 'users']);
    Route::put('/manage/documents/{document}/users/{user}', [ManageDocumentController::class, 'userAssignment']);
    Route::post('/manage/documents/{document}', [ManageDocumentController::class, 'update']);
    Route::get('/files/{document}', [FileController::class, 'get'])->name('getFile');
});
