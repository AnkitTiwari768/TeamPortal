<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait DashboardIaTrait
{
	
    public function getIaDashboardDetails($year = null, $fromDate = null, $toDate = null,$type=null)
    {
        return [
            'total_registered_ia' => $this->getTotalRegisteredIa($year, $fromDate, $toDate,$type),
            'total_pending_ia'    => $this->getTotalPendingIa($year, $fromDate, $toDate,$type),
            'total_verified_ia'   => $this->getTotalVerifiedIa($year, $fromDate, $toDate,$type),
            'total_rejected_ia'   => $this->getTotalRejectedIa($year, $fromDate, $toDate,$type),
        ];
    }


    public function getTotalRegisteredIa($year, $fromDate, $toDate,$type)
    {
		
        $total = DB::table('industrial_associations');
			
		 if (!empty($fromDate) && !empty($toDate)) {
			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);

		} else {
			 if ($type != 1) {
				
				$total->whereYear('created_at', $year);
			} 
		} 
			
		$total = $total->count();

        return $total;
    }


    public function getTotalPendingIa($year, $fromDate, $toDate,$type)
    {
		
        $total = DB::table('industrial_associations')->where('status', 1);
		
			
	   if (!empty($fromDate) && !empty($toDate)) {

			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);

		} else {

			if ($type != 1) {
				$total->whereYear('created_at', $year);
			}
		}
		
		$total = $total->count();

        return $total;
    }

    /* ---------------- VERIFIED ---------------- */

    public function getTotalVerifiedIa($year, $fromDate, $toDate,$type)
    {
		
        $total = DB::table('industrial_associations')->where('status', 2);
		
		if (!empty($fromDate) && !empty($toDate)) {

			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);

		} else {

			if ($type != 1) {
				$total->whereYear('created_at', $year);
			}
		}
		$total = $total->count();

        return $total;
    }

    /* ---------------- REJECTED ---------------- */

    public function getTotalRejectedIa($year, $fromDate, $toDate,$type)
    {
        		
        $total = DB::table('industrial_associations')->where('status', 3);
		
		if (!empty($fromDate) && !empty($toDate)) {

			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);

		} else {

			if ($type != 1) {
				$total->whereYear('created_at', $year);
			}
		}
		
		$total = $total->count();


        return $total;
    }

	
}
