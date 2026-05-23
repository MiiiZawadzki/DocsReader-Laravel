<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\HomeController;

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'data']);
});
