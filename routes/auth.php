<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Auth\PinController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    if (env('ALLOW_REGISTRATION', false)) {
        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [RegisteredUserController::class, 'store'])->middleware('honeypot');
    }

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware(\App\Http\Middleware\BlockAbusiveIps::class);

    // Magic-link sign-in (passwordless alternative)
    Route::get ('auth/magic-link',         [MagicLinkController::class, 'showRequest'])->name('magic-link.request');
    Route::post('auth/magic-link',         [MagicLinkController::class, 'sendLink'])
         ->middleware(['throttle:10,60', \App\Http\Middleware\BlockAbusiveIps::class])->name('magic-link.send');
    Route::get ('auth/magic-link/{token}', [MagicLinkController::class, 'consume'])
         ->where('token', '[A-Za-z0-9]{64}')->name('magic-link.consume');

    // PIN sign-in (first-name + PIN, ideal for elderly/non-tech members)
    Route::get ('pin', [PinController::class, 'showLogin'])->name('pin-login');
    Route::post('pin', [PinController::class, 'attempt'])->middleware(['throttle:20,15', \App\Http\Middleware\BlockAbusiveIps::class])->name('pin-login.attempt');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('honeypot')->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

});

// GET /logout — friendly confirmation page (works for both guests and authed users)
Route::view("logout", "auth.signout-confirm");
