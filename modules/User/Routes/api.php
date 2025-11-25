<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\SettingsController;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', UserController::class);

    Route::get('/settings', [SettingsController::class, 'data']);
    Route::post('/settings/user/name', [SettingsController::class, 'updateName']);
    Route::post('/settings/user/email', [SettingsController::class, 'updateEmail']);
    Route::post('/settings/user/password', [SettingsController::class, 'updatePassword']);
});
