<?php

use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\DocumentStatisticsController;
use Modules\Analytics\Http\Controllers\ManageStatisticsController;
use Modules\Analytics\Http\Controllers\UserStatisticsController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('statistics')->name('statistics.')->group(function () {

        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/stats',            [UserStatisticsController::class, 'stats'])->name('stats');
            Route::get('/documents/active', [UserStatisticsController::class, 'activeDocuments'])->name('documents.active');
            Route::get('/documents/total',  [UserStatisticsController::class, 'totalDocuments'])->name('documents.total');
            Route::get('/documents/read',   [UserStatisticsController::class, 'readDocuments'])->name('documents.read');
            Route::get('/charts',           [UserStatisticsController::class, 'charts'])->name('charts');
            Route::get('/read',             [UserStatisticsController::class, 'readStatistics'])->name('read');
        });

        Route::prefix('manage')->name('manage.')->group(function () {
            Route::get('/stats',            [ManageStatisticsController::class, 'stats'])->name('stats');
            Route::get('/documents/active', [ManageStatisticsController::class, 'activeDocuments'])->name('documents.active');
            Route::get('/documents/total',  [ManageStatisticsController::class, 'totalDocuments'])->name('documents.total');
            Route::get('/charts',           [ManageStatisticsController::class, 'charts'])->name('charts');
            Route::get('/read',             [ManageStatisticsController::class, 'readStatistics'])->name('read');

            Route::prefix('document/{document}')->name('document.')->group(function () {
                Route::get('/stats',    [DocumentStatisticsController::class, 'stats'])->name('stats');
                Route::get('/assigned', [DocumentStatisticsController::class, 'documentAssignment'])->name('assigned');
                Route::get('/reads',    [DocumentStatisticsController::class, 'documentReads'])->name('reads');
                Route::get('/ratio',    [DocumentStatisticsController::class, 'documentReadRatio'])->name('ratio');
                Route::get('/charts',   [DocumentStatisticsController::class, 'charts'])->name('charts');
                Route::get('/read',     [DocumentStatisticsController::class, 'readStatistics'])->name('read');
            });
        });
    });
});
