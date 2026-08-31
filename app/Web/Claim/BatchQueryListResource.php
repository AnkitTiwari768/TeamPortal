<?php

declare(strict_types=1);

namespace App\Web\Claim;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Domain\Batch\BatchStatus;
use DB;

class BatchQueryListResource extends JsonResource
{
    public function toArray($request)
    {
        [$totalAmount, $totalGstAmount, $totalSgstAmount, $totalCgstAmount, $totalTdsAmount, $totalClaimedAmount] = $this->calculateAmounts();

        return [
            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'financial_year' => $this->financial_year,
            'month' => $this->month,
            'month_name' => $this->month ? date('F', mktime(0, 0, 0, $this->month, 1)) : null,
            'description' => $this->description,
            'status' => BatchStatus::getLabelByValue($this->status),
            'created_at' => $this->created_at ?  date('d-m-Y', strtotime($this->created_at)) : null,
            'total_claims' =>$this->getTotalClaims(),
            'claim_amount' => $totalAmount - ($totalGstAmount ?? 0),
            'gst_amount' => $totalGstAmount,
            'sgst_amount' => $totalSgstAmount,
            'cgst_amount' => $totalCgstAmount,
            'tds_amount' => $totalTdsAmount,
            'total_claimed_amount' => $totalClaimedAmount,
            'snp_name'   => $this->snp_name,
            'snp_id'   => $this->snp_id,
			'batch_id'   => $this->batch_id,
			'is_query'   => $this->is_query,
        ];
    }
	
	
	protected function getTotalClaims()
    {
		//return DB::table('dy_batch_claims as bc') ->join('claims as c', 'c.id', '=', 'bc.claim_id')->where('bc.batch_id', $this->id)->where('bc.is_deleted',1)->count();
		

        return DB::table('dy_batch_claims as bc')->join('dy_batches as b', 'b.id', '=', 'bc.batch_id')->where('bc.batch_id', $this->id)->where('bc.is_deleted',1)->whereIn('b.is_query',[1,2])->where('bc.status', BatchStatus::REJECTED_NSIC_FINANCE->value)->count();
    }
	
	
	protected function getTotalAmount()
    {
		 $total_amount = DB::table('dy_batch_claims as bc')
		->join('dy_batches as b', 'b.id', '=', 'bc.batch_id')
		->join('claims as c', 'c.id', '=', 'bc.claim_id')
		->where('bc.batch_id', $this->id)
		->where('bc.is_deleted', 1)
		->whereIn('b.is_query', [1, 2])
		->where('bc.status', BatchStatus::REJECTED_NSIC_FINANCE->value)
		->sum('c.amount');   // 🔥 use sum() directly instead of value(DB::raw())

	return $total_amount > 0 ? $total_amount : 0;
		
       /*$total_amount = DB::table('dy_batch_claims as bc') ->join('claims as c', 'c.id', '=', 'bc.claim_id')->where('bc.batch_id', $this->id)->whereNull('bc.is_deleted')->value(DB::raw('SUM(c.amount)'));
		return $total_amount > 0 ? $total_amount : 0;*/
    }

	protected function calculateAmounts()
	{
		$result = DB::table('dy_batch_claims as bc')
			->join('dy_batches as b', 'b.id', '=', 'bc.batch_id')
			->join('claims as c', 'c.id', '=', 'bc.claim_id')
			->where('bc.batch_id', $this->id)
			->where('bc.is_deleted', 1)
			->whereIn('b.is_query', [1, 2])
			->where('bc.status', BatchStatus::REJECTED_NSIC_FINANCE->value)
			->select(
				DB::raw('SUM(c.amount) as total_amount'),
				DB::raw('SUM(c.gst_amount) as total_gst_amount'),
				DB::raw('SUM(c.sgst_amount) as total_sgst_amount'),
				DB::raw('SUM(c.cgst_amount) as total_cgst_amount'),
				DB::raw('SUM(c.tds_amount) as total_tds_amount'),
				DB::raw('SUM(c.total_claimed_amount) as total_claimed_amount')
			)
			->first();

		return [
			$result->total_amount ?? 0,
			$result->total_gst_amount ?? 0,
			$result->total_sgst_amount ?? 0,
			$result->total_cgst_amount ?? 0,
			$result->total_tds_amount ?? 0,
			$result->total_claimed_amount ?? 0
		];
	}
}
