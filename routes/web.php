<?php

use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Password Reset Routes
Route::get('/reset-password', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.reset.submit');
Route::get('/password-reset-success', function () {
    return view('auth.password-reset-success');
})->name('password.reset.success');

// Email Verification Routes  
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');
Route::get('/email-verified', [VerifyEmailController::class, 'verified'])->name('email.verified');

// Optional: Login page route (if you want to create one later)
Route::get('/login', function () {
    return redirect('/');
})->name('login');

