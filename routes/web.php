<?php

use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Zoho OAuth Callback
Route::get('/callback', function () {
    $code = request('code');
    $error = request('error');
    
    if ($error) {
        return response()->json([
            'error' => 'Authorization failed',
            'details' => $error
        ], 400);
    }
    
    if ($code) {
        return response()->json([
            'success' => true,
            'message' => 'Authorization successful! Copy this code and run: php artisan zoho:auth get-token',
            'code' => $code
        ]);
    }
    
    return response()->json([
        'error' => 'No authorization code received'
    ], 400);
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

