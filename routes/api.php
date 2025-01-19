<?php

use App\Data\DTO\UserDTO;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return new UserDTO(
            $request->user()->load(['userPermissions', 'userPermissions.permission'])
        );
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('documents', DocumentController::class);
    Route::get('/files/{document}', [FileController::class, 'get'])->name('getFile');
});
