<?php

use Illuminate\Support\Facades\Route;

use App\Web\Country\CountryController;

Route::group(['middleware' => 'auth'], function() {
    //Route::get('getCountryName/{id}', [CountryController::class,'getCountryName']);
    Route::controller(CountryController::class)->group(function() {
        Route::get('/countries',  'index')->name('country.index');
        Route::get('/countries/datalist', 'getCountries');
        Route::get('countries/create', 'create');
        Route::get('countries/{id}/edit', 'edit');
        Route::post('/countries', 'createCountry');
        Route::post('/countries/update/{id}', 'updateCountry');
    });
});