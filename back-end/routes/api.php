<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FeedbackController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    Route::post('/documents/upload', [DocumentController::class, 'upload']);
    Route::get('/documents/{document}/decision', [DocumentController::class, 'getDecision']);
    Route::post('/documents/{document}/feedback', [FeedbackController::class, 'submitFeedback']);
});

