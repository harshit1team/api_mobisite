<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\AreaController;
use App\Http\Controllers\Api\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Api\BlogController as PublicBlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/blogs', [PublicBlogController::class, 'index']);
Route::get('/blogs/{slug}', [PublicBlogController::class, 'show']);
Route::get('/areas', [AreaController::class, 'index']);

// Admin routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Area management
        Route::get('/areas', [AreaController::class, 'index']);
        Route::post('/areas', [AreaController::class, 'store']);
        Route::put('/areas/{area}', [AreaController::class, 'update']);
        Route::delete('/areas/{area}', [AreaController::class, 'destroy']);

        // Blog management
        Route::get('/blogs', [AdminBlogController::class, 'index']);
        Route::post('/blogs', [AdminBlogController::class, 'store']);
        Route::get('/blogs/{blog}', [AdminBlogController::class, 'show']);
        Route::put('/blogs/{blog}', [AdminBlogController::class, 'update']);
        Route::delete('/blogs/{blog}', [AdminBlogController::class, 'destroy']);
        Route::post('/blogs/upload-image', [AdminBlogController::class, 'uploadImage']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
