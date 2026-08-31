<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$durations = DB::table('attribute_values')->where('attribute_value', 'like', '%Quarter%')->get();
$json = json_encode($durations, JSON_PRETTY_PRINT);
file_put_contents('quarters.json', $json);
echo "Done";
