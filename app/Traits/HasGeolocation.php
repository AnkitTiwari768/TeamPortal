<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasGeolocation
{
    public function getStates(?bool $skipNational = false): array
    {
        $query = DB::table('states')->where('status', true);
        if ($skipNational) {
            $query->whereRaw("LOWER(TRIM(name)) != 'national'");
        }
        return $query->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
