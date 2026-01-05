<?php

use Illuminate\Support\Facades\Route;
use Modules\History\Http\Controllers\DocumentsHistoryController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/documents/history', [DocumentsHistoryController::class, 'data']);
    Route::post('document-reads/{document}', [DocumentsHistoryController::class, 'markAsRead']);
});
