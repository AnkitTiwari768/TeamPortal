<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('workshops');
echo json_encode($columns, JSON_PRETTY_PRINT);

$workshop = \App\Web\Workshop\Workshop::latest()->first();
echo "\n\n";
echo json_encode($workshop ? $workshop->toArray() : [], JSON_PRETTY_PRINT);
