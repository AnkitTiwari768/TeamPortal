<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseQuery = \Illuminate\Support\Facades\DB::table('team_msme_schemes as tms')
    ->where('tms.select_snp', 0)
    ->whereNull('tms.bpp_id')
    ->whereNotNull('tms.major_activity')
    ->where('tms.major_activity', '!=', '');

$count1 = (clone $baseQuery)->count();
echo "Open MSE Count: " . $count1 . "\n";

$baseQuery2 = \Illuminate\Support\Facades\DB::table('team_msme_schemes as tms')
    ->where('tms.select_snp', 0)
    ->whereNull('tms.bpp_id')
    ->whereNotNull('tms.major_activity')
    ->where('tms.major_activity', '!=', '')
    ->whereYear('tms.created_at', now()->year);

$count2 = (clone $baseQuery2)->count();
echo "Open MSE Count (This Year): " . $count2 . "\n";
