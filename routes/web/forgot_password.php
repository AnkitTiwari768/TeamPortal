<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::get('/forget-password', [ForgotPasswordController::class, 'index'])->name('forget-password');
Route::post('/forget-password', [ForgotPasswordController::class, 'store'])->name('forget-password'); 
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'resetPassword']);
Route::post('/reset-password', [ForgotPasswordController::class, 'storeResetPassword'])->name('reset-password');


/*Route::post('reset-password-mobile', [ForgotPasswordController::class, 'reset_password_mobile'])->name('reset-password-mobile');  
Route::get('resetpassword', [ForgotPasswordController::class, 'resetPasswordByMobile'])->name('resetPasswordByMobile');
Route::post('verify_otp_forgotPassword', [ForgotPasswordController::class, 'verify_otp']); 
Route::post('resend-otp-forgotPassword', [ForgotPasswordController::class, 'resendOtp'])->name('resend-otp-forgotPassword');*/