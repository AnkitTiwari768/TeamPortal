<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundDistributionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'financial_year'         => $this->financial_year,
            'duration'               => $this->duration_name ?? '-',
            'sub_duration'           => $this->sub_duration_name ?? '-',
            'sanction_order_no'      => $this->sanction_order_number ?? '-',
            'sanction_order_date'    => $this->sanction_order_date ? Carbon::parse($this->sanction_order_date)->format('d-m-Y') : '-',
            'distribution_amount'    => $this->distribution_amount,
            'tds_percentage'         => $this->tds_percentage,
            'tds_amount'             => $this->tds_amount,
            'net_payable_amount'     => $this->net_payable_amount,
            'created_at'             => $this->created_at ? Carbon::parse($this->created_at)->format('d-m-Y') : '-',
        ];
    }
}
