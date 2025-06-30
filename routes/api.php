<?php

use App\Data\DTO\UserDTO;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ManageDocumentController;
use App\Http\Controllers\Api\SettingsController;
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
    Route::get('/manage/documents/{document}/users', [ManageDocumentController::class, 'users']);
    Route::put('/manage/documents/{document}/users/{user}', [ManageDocumentController::class, 'userAssignment']);
    Route::post('/manage/documents/{document}', [ManageDocumentController::class, 'update']);
    Route::get('/files/{document}', [FileController::class, 'get'])->name('getFile');

    Route::get('/settings', [SettingsController::class, 'data']);
    Route::post('/settings/user/name', [SettingsController::class, 'updateName']);
    Route::post('/settings/user/email', [SettingsController::class, 'updateEmail']);
    Route::post('/settings/user/password', [SettingsController::class, 'updatePassword']);
});
