<?php 

declare(strict_types=1);

namespace App\Web\MisReport;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MisReportResource extends JsonResource
{
    public function toArray($request)
    {
        $createdAt = Carbon::parse($this->created_at);
        $expiryDate = $createdAt->addDays(30);

        // Handle multiple category IDs
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
            'udyam_no' => $this->udyam_no,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'entrepreneur_name' => $this->entrepreneur_name,
            'enterprise_name' => $this->enterprise_name,
            'organisation_type' => $this->organisation_type,
            'msme_classification' => $this->msme_classification,
            'social_category' => $this->social_category,
            'state_name' => $this->state_name ?? null,
            'team_id' => $this->team_id ?? null,
            'major_activity' => $this->major_activity,
            'product_categories' => $categoryNames, 
            'gender'=> $this->gender,
            'total_emp'=> $this->total_emp,
            'net_investment_plant_machinery'=>$this->net_investment_plant_machinery,
            'turnover'=>$this->turnover,
            'incorporation_date'=>$this->incorporation_date,
            'created_at' => date('d-m-Y', strtotime($this->created_at)),
            'days_left' => Carbon::now()->diffInDays($expiryDate, false),
            'snp_name' => $this->snp_name ?? null,
            'snp_id' => $this->snp_id ?? null,
            'transaction_type' => $this->transaction_type ?? null,
        ];
    }
}