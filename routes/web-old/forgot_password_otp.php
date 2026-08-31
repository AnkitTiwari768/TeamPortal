<?php

use Illuminate\Support\Facades\Route;
use App\Domain\Auth\ForgotPasswordOtpController;

Route::get('/forgot-password-otp', [ForgotPasswordOtpController::class, 'index'])->name('forgot-password-otp');
Route::post('/forgot-password-otp-send', [ForgotPasswordOtpController::class, 'sendOtp'])->name('forgot-password-otp-send');
Route::get('/forgot-password-otp-verify', [ForgotPasswordOtpController::class, 'showVerifyForm'])->name('forgot-password-otp-verify');
Route::post('/forgot-password-otp-verify', [ForgotPasswordOtpController::class, 'verifyOtp'])->name('forgot-password-otp-verify-post');
Route::get('/forgot-password-otp-reset', [ForgotPasswordOtpController::class, 'showResetForm'])->name('forgot-password-otp-reset');
Route::post('/forgot-password-otp-reset', [ForgotPasswordOtpController::class, 'resetPassword'])->name('forgot-password-otp-reset-post');
Route::get('/forgot-password-otp-success', [ForgotPasswordOtpController::class, 'showSuccess'])->name('forgot-password-otp-success');
Route::get('/forgot-password-otp-check-lock', [ForgotPasswordOtpController::class, 'checkLockStatus'])->name('forgot-password-otp-check-lock');
