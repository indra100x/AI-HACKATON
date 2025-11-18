<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::get('/dashboard', [AuthController::class, 'dashboard']);

    // Document routes
    Route::post('/documents/upload', [DocumentController::class, 'upload']);
    Route::get('/documents', [DocumentController::class, 'getUserDocuments']);
    Route::get('/documents/{document}', [DocumentController::class, 'getDocument']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'downloadDocument']);
    Route::delete('/documents/{document}', [DocumentController::class, 'deleteDocument']);
});

