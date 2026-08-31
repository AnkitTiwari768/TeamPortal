<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Open MSE: ' . \Illuminate\Support\Facades\DB::table('team_msme_schemes')->where('select_snp', 0)->whereNull('bpp_id')->whereNotNull('major_activity')->where('major_activity', '!=', '')->count() . "\n"; 
echo 'Onboarded MSE: ' . \Illuminate\Support\Facades\DB::table('team_msme_schemes as tms')->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')->where('tsm.status', 1)->whereNotNull('tms.major_activity')->where('tms.major_activity', '!=', '')->distinct()->count('tms.id') . "\n";
echo 'Registered: ' . \Illuminate\Support\Facades\DB::table('team_msme_schemes')->whereNotNull('major_activity')->where('major_activity', '!=', '')->count() . "\n";
echo 'Categories onboarded: ' . \Illuminate\Support\Facades\DB::table('team_msme_schemes as tms')->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')->where('tsm.status', 1)->count() . "\n";
echo 'Categories registered: ' . \Illuminate\Support\Facades\DB::table('team_msme_schemes')->count() . "\n";
