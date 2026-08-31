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
echo "Open MSE count(): " . $count1 . "\n";

$count2 = (clone $baseQuery)->distinct()->count('tms.id');
echo "Open MSE distinct()->count('tms.id'): " . $count2 . "\n";

$ids = (clone $baseQuery)->pluck('tms.id')->toArray();
$uniqueIds = array_unique($ids);
echo "Count of IDs: " . count($ids) . "\n";
echo "Count of unique IDs: " . count($uniqueIds) . "\n";

if (count($ids) != count($uniqueIds)) {
    echo "Duplicate IDs found:\n";
    $counts = array_count_values($ids);
    foreach ($counts as $id => $c) {
        if ($c > 1) {
            echo "ID $id appears $c times\n";
        }
    }
}
