<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\AuthController;


Route::prefix('v1')->group(function () {

    // API for login
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Adding Middleware for authenticate users
    Route::middleware('auth:api')->group(function () {
        // Authenticated user action routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Task Controller routes
        Route::apiResource('tasks', TaskController::class);
        Route::patch('/tasks/{id}/restore', [TaskController::class, 'restore']);
        Route::patch('/tasks/{task}/toggle-status', [TaskController::class, 'toggleStatus']);

        // Tag Controller Routes
        Route::apiResource('tags', TagController::class)->except(['show']);

        // User Controller Routes, for the form and filters we need the list of users
        Route::get('users', [UserController::class, 'index']);
    });
});
