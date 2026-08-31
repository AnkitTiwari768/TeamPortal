<?php

use App\Web\RouteList\RouteListController;
use Illuminate\Support\Facades\Route;

Route::get('/route-list', [RouteListController::class, 'index'])->name('route-list.index');
Route::get('/route-list/data', [RouteListController::class, 'data'])->name('route-list.data');
