<?php

use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\DocumentStatisticsController;
use Modules\Analytics\Http\Controllers\ManageStatisticsController;
use Modules\Analytics\Http\Controllers\UserStatisticsController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('statistics')->name('statistics')->group(function () {
        Route::prefix('user')->name('user')->group(function () {
            Route::get('/stats', [UserStatisticsController::class, 'stats']);
            Route::get('/documents/active', [UserStatisticsController::class, 'activeDocuments']);
            Route::get('/documents/total', [UserStatisticsController::class, 'totalDocuments']);
            Route::get('/documents/read', [UserStatisticsController::class, 'readDocuments']);


            Route::get('/charts', [UserStatisticsController::class, 'charts']);
            Route::get('/read', [UserStatisticsController::class, 'readStatistics']);
        });
        Route::prefix('manage')->name('manage')->group(function () {
            Route::get('/stats', [ManageStatisticsController::class, 'stats']);
            Route::get('/documents/active', [ManageStatisticsController::class, 'activeDocuments']);
            Route::get('/documents/total', [ManageStatisticsController::class, 'totalDocuments']);

            Route::get('/charts', [ManageStatisticsController::class, 'charts']);
            Route::get('/read', [ManageStatisticsController::class, 'readStatistics']);

            Route::prefix('document/{document}')->name('manage')->group(function () {
                Route::get('/stats', [DocumentStatisticsController::class, 'stats']);
                Route::get('/assigned', [DocumentStatisticsController::class, 'documentAssignment']);
                Route::get('/reads', [DocumentStatisticsController::class, 'documentReads']);
                Route::get('/ratio', [DocumentStatisticsController::class, 'documentReadRatio']);

                Route::get('/charts', [DocumentStatisticsController::class, 'charts']);
                Route::get('/read', [DocumentStatisticsController::class, 'readStatistics']);
            });
        });
    });
});
