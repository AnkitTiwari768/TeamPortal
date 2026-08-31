<?php

use Illuminate\Support\Facades\Route;

use App\Web\Language;
use App\Web\Language\LanguageController;


Route::post('/get-language', [LanguageController::class, 'getlanguage']);
Route::group(['middleware' => 'auth.session'], function () {
    Route::controller(LanguageController::class)->group(function () {
            Route::get('/language',  'index')->name('language.index');
            Route::get('/language/datalist', 'getDataLanguage')->name('language.datalist');
            Route::get('language-create', 'create')->name('language-create');
            Route::get('language/{id}/edit', 'edit')->name('language.edit');
            Route::post('/language-create', 'languageCreate')->name('language-create');
            Route::post('/language-update/{id}', 'languageUpdate')->name('language-update');
    });
});

