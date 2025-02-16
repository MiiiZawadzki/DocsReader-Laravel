<?php

use App\Data\DTO\UserDTO;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ManageDocumentController;
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

    Route::get('/manage/documents', [ManageDocumentController::class, 'index']);
    Route::get('/manage/documents/{document}', [ManageDocumentController::class, 'show']);
    Route::post('/manage/documents/{document}', [ManageDocumentController::class, 'update']);
    Route::get('/files/{document}', [FileController::class, 'get'])->name('getFile');
});
