<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MsmeAllListResource extends JsonResource
{
    public function toArray($request)
    {
        $categoryNames = [];
        if (!empty($this->product_category_id)) {
            $ids = json_decode($this->product_category_id, true);
            if (is_array($ids)) {
                $categoryNames = DB::table('sub_domains')
                    ->whereIn('id', $ids)
                    ->pluck('name')
                    ->toArray();
            }
        }

        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'udyam_no' => $this->udyam_no,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'entrepreneur_name' => $this->entrepreneur_name,
            'enterprise_name' => $this->enterprise_name,
            'organisation_type' => $this->organisation_type,
            'major_activity' => $this->major_activity,
            'product_categories' => $categoryNames,
            'state_name' => $this->state_name ?? null,
            'created_at' => date('d-m-Y', strtotime($this->created_at)),
            'incorporation_date' => $this->incorporation_date,
            'transaction_type' => $this->transaction_type ?? null,
        ];
    }
}
