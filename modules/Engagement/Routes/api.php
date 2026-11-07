<?php

use Illuminate\Support\Facades\Route;
use Modules\Engagement\Http\Controllers\ReadingSessionController;
use Modules\Engagement\Http\Middleware\DecodeBeaconPayload;

Route::middleware(['auth', 'throttle:120,1'])->group(function () {
    Route::post('reading-sessions', [ReadingSessionController::class, 'start']);
    Route::post('reading-sessions/{uuid}/ticks', [ReadingSessionController::class, 'record'])->middleware(DecodeBeaconPayload::class);
    Route::post('reading-sessions/{uuid}/end', [ReadingSessionController::class, 'end'])->middleware(DecodeBeaconPayload::class);
});
