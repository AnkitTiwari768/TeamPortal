<?php

declare(strict_types=1);

namespace App\Domain\Msme;

use Illuminate\Support\Facades\DB;

class MsmeDetailsAction
{
    public function execute(string $userId)
    {
        $result = DB::table('team_msme_schemes as msme')
            ->leftJoin('states as s', 's.id', '=', 'msme.state_id')
            ->leftJoin('locations as d', 'd.id', '=', 'msme.district_id')
            ->leftJoin('attribute_values as av', 'av.id', '=', 'msme.msme_classification')
            ->select([
                'msme.*',
                's.name as state_name',
                'd.name as district_name',
                'av.attribute_value as msme_classification_name',
            ])
            ->where('user_id', $userId)
            ->first();

        if (!$result) {
            return [];
        }

        $result->existing_activity_details = $result->existing_activity_details ? json_decode($result->existing_activity_details, true) : [];
        $result->product_details =   $result->product_details ? json_decode($result->product_details, true) : [];
        $result->product_category_id = $result->product_category_id ? json_decode($result->product_category_id, true) : [];
        $result->activity_details = $result->activity_details ? json_decode($result->activity_details, true) : [];
        $result->enterprise_details = $result->enterprise_details ? json_decode($result->enterprise_details, true) : [];

        return $result;
    }
}
