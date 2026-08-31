<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = new \Illuminate\Http\Request();
$req->merge(['type' => 1]);

$ctrl = app(\App\Web\Dashboard\AdminDashboardController::class);
$resp = $ctrl->getMsmeCategoryCountOnboarded($req);

echo $resp->content() . "\n";
