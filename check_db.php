<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('workshops');
echo "Columns in workshops:\n";
print_r($columns);

$workshop = \App\Web\Workshop\Workshop::latest()->first();
echo "Latest workshop:\n";
print_r($workshop->toArray());
