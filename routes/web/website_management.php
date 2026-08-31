<?php
use Illuminate\Support\Facades\Route;
use App\Web\Menu\MenusController;
use App\Web\Page\PageController;
use App\Web\Files\FilesController;
use App\Web\Slider\SliderController;
use App\Web\FileUpload\FileUploadController;   


Route::group(['middleware' => 'auth'], function() {    
    Route::get('/menus/datalist', [MenusController::class, 'datalist']);
    Route::resource('/menus', MenusController::class)->except(['show']);
    Route::get('/menus/get_existed_menu_order/{id}', [MenusController::class, 'get_existed_menu_order']);

    Route::get('/sliders/datalist', [SliderController::class, 'datalist']);
    Route::resource('/sliders', SliderController::class)->except(['show']);
    Route::post('SliderImage', [FileUploadController::class, 'SliderImage']);
    Route::post('deleteSliderImage', [FileUploadController::class, 'deleteSliderImage']);

    
    Route::get('/pages/datalist', [PageController::class, 'datalist']);
    Route::resource('/pages', PageController::class)->except(['show']);

    Route::get('/media', [FilesController::class, 'index'])->name('media');
    Route::get('/media/datalist', [FilesController::class, 'datalist']);
    Route::get('/media/create', [FilesController::class, 'create']);
    Route::post('/media/store', [FilesController::class, 'store']);
    Route::post('/media/UploadFile', [FilesController::class, 'UploadFile']);
    Route::post('/media/deleteUploadFile', [FilesController::class, 'deleteUploadFile']);
});