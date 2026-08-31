<?php

use Illuminate\Support\Facades\Route;
use App\Web\EmailTemplate\EmailTemplateController;

Route::group(['middleware' => 'auth'], function () {

    Route::controller(EmailTemplateController::class)->group(function () {

        Route::get('/email-template', 'index')->name('email-template-list');
        Route::get('/email-template/create', 'create')->name('email-template-create');
        Route::post('/email-template/store', 'store')->name('email-template-store');
        Route::get('/email-template/edit/{id}', 'edit')->name('email-template-edit');
        Route::post('/email-template/update/{id}', 'update')->name('email-template-update');

    });

});