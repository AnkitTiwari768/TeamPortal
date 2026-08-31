<?php

use Illuminate\Support\Facades\Route;

use App\Web\ProductDomainMapping;

Route::post('/get-product-domain-type', [ProductDomainMappingController::class, 'getProductDomainType']);
