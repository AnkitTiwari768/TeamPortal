<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetDashboardDetailsAction
{
    public function execute($year = null, $fromDate = null, $toDate = null,$type=null)
    {
        $totalMseRegistered = $this->getTotalMseRegistered($year, $fromDate, $toDate,$type);
        $totalMseMapped = $this->getTotalMseMapped($year, $fromDate, $toDate,$type);

        return [
            'totalMseRegistered' => $totalMseRegistered,
            'totalMseMapped' => $totalMseMapped
        ];
    }



    public function getTotalMseRegistered($year, $fromDate, $toDate,$type)
    {
		
        $query = DB::table('team_msme_schemes')->where('created_by', authId());

        /* if ($year = request()->query('year')) {
            $query->whereYear('created_at', $year);
        }

        if ($fromDate = request()->query('from_date')) {
            $fromDate = Carbon::parse($fromDate)->startOfDay()->format('Y-m-d H:i:s');
            $query->where('created_at', '>=', $fromDate);
        }

        if ($toDate = request()->query('to_date')) {
            $toDate = Carbon::parse($toDate)->endOfDay()->format('Y-m-d H:i:s');
            $query->where('created_at', '<=', $toDate);
        } */
		
		// if ($fromDate && $toDate) {

        //     $query->whereBetween('created_at', [
        //         Carbon::parse($fromDate)->startOfDay(),
        //         Carbon::parse($toDate)->endOfDay(),
        //     ]);
        // } else {
        //     if ($type != 1) {
		// 		$query->whereYear('created_at', $year);
		// 	}
        // }

        return $query->count();
    }



    public function getTotalMseMapped($year, $fromDate, $toDate,$type)
    {
        $query = DB::table('team_msme_schemes as a')
            ->join('team_snpmsme_mapping as b', 'a.id', '=', 'b.msme_id')
			->where('b.status',1)
            ->where('a.created_by', authId());

        /* if ($year = request()->query('year')) {
            $query->whereYear('a.created_at', $year);
        }

        if ($fromDate = request()->query('from_date')) {
            $fromDate = Carbon::parse($fromDate)->startOfDay();
            $query->where('a.created_at', '>=', $fromDate);
        }

        if ($toDate = request()->query('to_date')) {
            $toDate = Carbon::parse($toDate)->endOfDay();
            $query->where('a.created_at', '<=', $toDate);
        } */
		
		// if ($fromDate && $toDate) {

        //     $query->whereBetween('a.created_at', [
        //         Carbon::parse($fromDate)->startOfDay(),
        //         Carbon::parse($toDate)->endOfDay(),
        //     ]);
        // } else {
        //     if ($type != 1) {
		// 		$query->whereYear('a.created_at', $year);
		// 	}
        // }

        return $query->count();
    }
}
