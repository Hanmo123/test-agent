<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ResourceController;
use App\Http\Controllers\Api\V1\WorkController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\DraftController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\DailyPhotoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/works', [WorkController::class, 'index']);
    Route::get('/works/{work}', [WorkController::class, 'show']);
    Route::get('/daily-photo', [DailyPhotoController::class, 'today']);
    Route::get('/users/{user}/profile', [UserController::class, 'profile']);

    // Quiz routes (require auth but not level 1)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/auth/quiz', [AuthController::class, 'quiz']);
        Route::post('/auth/quiz', [AuthController::class, 'submitQuiz']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });

    // Protected routes (require auth and level 1+)
    Route::middleware(['auth:sanctum', 'user.level:1'])->group(function () {
        // User management
        Route::patch('/users/me', [UserController::class, 'updateProfile']);
        Route::patch('/users/me/preferences', [UserController::class, 'updatePreferences']);
        Route::post('/users/{user}/follow', [UserController::class, 'follow']);
        Route::delete('/users/{user}/follow', [UserController::class, 'unfollow']);

        // Resources
        Route::get('/resources', [ResourceController::class, 'index']);
        Route::post('/resources/upload', [ResourceController::class, 'upload']);
        Route::get('/resources/{resource}', [ResourceController::class, 'show']);
        Route::get('/resources/{resource}/download', [ResourceController::class, 'download']);
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy']);

        // Drafts
        Route::get('/drafts', [DraftController::class, 'index']);
        Route::post('/drafts', [DraftController::class, 'store']);
        Route::get('/drafts/{draft}', [DraftController::class, 'show']);
        Route::patch('/drafts/{draft}', [DraftController::class, 'update']);
        Route::delete('/drafts/{draft}', [DraftController::class, 'destroy']);

        // Works
        Route::post('/works', [WorkController::class, 'store']);
        Route::patch('/works/{work}', [WorkController::class, 'update']);
        Route::delete('/works/{work}', [WorkController::class, 'destroy']);
        Route::post('/works/{work}/like', [WorkController::class, 'like']);
        Route::delete('/works/{work}/like', [WorkController::class, 'unlike']);

        // Collections
        Route::get('/collections', [CollectionController::class, 'index']);
        Route::post('/collections', [CollectionController::class, 'store']);
        Route::get('/collections/{collection}', [CollectionController::class, 'show']);
        Route::patch('/collections/{collection}', [CollectionController::class, 'update']);
        Route::delete('/collections/{collection}', [CollectionController::class, 'destroy']);
        Route::post('/collections/{collection}/works', [CollectionController::class, 'addWork']);
        Route::delete('/collections/{collection}/works/{work}', [CollectionController::class, 'removeWork']);

        // Shutter time
        Route::post('/shutter-time/claim-daily', [UserController::class, 'claimDailyShutterTime']);
    });
});