<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$modules = \DB::table('modules')->where('name', 'like', '%MSE%')->orWhere('name', 'like', '%MIS%')->get();
echo "Modules:\n";
print_r($modules->toArray());

$permissions = \DB::table('permissions')->whereIn('module_id', $modules->pluck('id'))->get();
echo "\nPermissions:\n";
print_r($permissions->toArray());
