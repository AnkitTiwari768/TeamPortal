<?php

use App\Domain\UserProfile\UserProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\ApplicantProfileController;

Route::group(['middleware' => 'auth'], function () {
    // Route::get('/profile', [ProfileController::class, 'index']);
    Route::get('/profile', [UserProfileController::class, 'index']);
    Route::get('/profile-history', [UserProfileController::class, 'histories']);
    Route::get('/profile-snapshot/{id}', [UserProfileController::class, 'show']);
    Route::post('/update-custom-user-profile/{id}', [ProfileController::class, 'updateCustomUserProfile']);
    Route::post('/profile/update/{id}', [ProfileController::class, 'update']);
    Route::post('/profilePhoto', [ProfileController::class, 'profilePhoto']);
    Route::post('/profile/update-photo/{id}', [ProfileController::class, 'updatePhoto']);
    Route::get('/applicant_profile', [ApplicantProfileController::class, 'index']);
    Route::post('/applicant_profile/update/{id}', [ApplicantProfileController::class, 'update']);
});
