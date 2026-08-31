<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$colsAlloc = Illuminate\Support\Facades\Schema::getColumnListing('fund_allocations');
$colsMap = Illuminate\Support\Facades\Schema::getColumnListing('fund_allocations_map');
file_put_contents('schema_out.txt', json_encode(['fund_allocations' => $colsAlloc, 'fund_allocations_map' => $colsMap]));
