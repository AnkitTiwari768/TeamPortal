<?php 

declare(strict_types=1);

namespace App\Web\FundFlow;

use Illuminate\Http\Resources\Json\JsonResource;

class AllocationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id, 
            'financial_year' => $this->financial_year,
            'duration' => $this->duration,
			'duration_limit' => $this->duration_limit,
            'total_amount_allocated' => $this->total_amount_allocated??null,
            'amount_allocated' =>$this->amount_allocated??null,
            'majorComponent' => $this->majorComponent??null,
            'component' => $this->component??null,
            'subComponent' => $this->subComponent??null,
            'sanction_order_no' => $this->sanction_order_no,
            'sanction_order_date'    => $this->sanction_order_date ? date('d-m-Y', strtotime($this->sanction_order_date)) : null,
            'created_at'             => $this->created_at ? date('d-m-Y', strtotime($this->created_at))  : null,
            'payable_amount' => $this->payable_amount ?? null,
            'tds' => $this->tds ?? null,
        ];
    }
}