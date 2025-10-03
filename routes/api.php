<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
    Route::get('me', 'me');
    
    // Password Reset Routes
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
    
    // Email Verification Routes
    Route::get('email/verify/{id}/{hash}', 'verifyEmail')->name('verification.verify');
    Route::post('email/resend', 'resendVerification');
    
    // Profile Update Routes (Protected)
    Route::middleware('auth:api')->group(function () {
        Route::put('profile', 'updateProfile');
        Route::put('password', 'updatePassword');
        Route::post('image', 'updateImage');
    });
});

// Protected API routes
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Task management routes
    Route::apiResource('tasks', TaskController::class);
    
});