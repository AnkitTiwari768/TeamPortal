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

$categoriesRaw = (clone $baseQuery)->pluck('tms.product_category_id', 'tms.id');

$totalRaw = count($categoriesRaw);
echo "Total rows: " . $totalRaw . "\n";

$nullCount = 0;
$emptyStringCount = 0;
$invalidJsonCount = 0;

$categoryCounts = [];
foreach ($categoriesRaw as $id => $jsonStr) {
    if ($jsonStr === null) {
        $nullCount++;
        echo "MSME ID $id has NULL product_category_id\n";
        continue;
    }
    if ($jsonStr === '') {
        $emptyStringCount++;
        echo "MSME ID $id has empty string product_category_id\n";
        continue;
    }

    $ids = json_decode((string) $jsonStr, true);
    if (is_array($ids)) {
        foreach ($ids as $cid) {
            $categoryCounts[$cid] = ($categoryCounts[$cid] ?? 0) + 1;
        }
    } else {
        $categoryCounts[$jsonStr] = ($categoryCounts[$jsonStr] ?? 0) + 1;
    }
}

echo "Nulls: $nullCount, Empties: $emptyStringCount\n";

$subDomains = \Illuminate\Support\Facades\DB::table('sub_domains')
    ->whereIn('id', array_keys($categoryCounts))
    ->pluck('name', 'id');

$validCategoryCounts = [];
foreach ($categoryCounts as $id => $count) {
    if (isset($subDomains[$id])) {
        $validCategoryCounts[$id] = $count;
    } else {
        echo "Invalid SubDomain ID: $id (count: $count)\n";
    }
}
