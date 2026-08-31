<?php

declare(strict_types=1);

namespace App\Web\MisReport;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MsmeBulkResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'udyam_no' => $this->udyam_no,
            'mobile' => $this->mobile,
            // 'email' => $this->email,
            'transaction_type' => $this->ondc_transaction_type_id,
            'status' => $this->status,
            'product_category_id' => $this->product_category_id,
            'name' => $this->name,
            // 'enterprise_name' => $this->enterprise_name,
            // 'team_id' => $this->team_id,
            // 'state_name' => $this->state_name,
            'created_at' => \Carbon\Carbon::parse($this->created_at)->format('d-m-Y'),
            // 'registration_date' => \Carbon\Carbon::parse($this->registration_date)->format('d-m-Y'),
        ];
        
    }
}