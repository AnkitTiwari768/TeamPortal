<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait DashboardSnpTrait
{
	
    public function getSnpDashboardDetails($slug,$year = null, $fromDate = null, $toDate = null,$type=null)
    {
        return [
            'total_registered_sbl' => $this->getTotalRegisteredSNPs($slug,$year, $fromDate, $toDate,$type),
            'total_pending_sbl'    => $this->getTotalPendingSNPs($slug,$year, $fromDate, $toDate,$type),
            'total_verified_sbl'   => $this->getTotalVerifiedSNPs($slug,$year, $fromDate, $toDate,$type),
            'total_rejected_sbl'   => $this->getTotalRejectedSNPs($slug,$year, $fromDate, $toDate,$type),
            'total_reverted_sbl'   => $this->getTotalRevertedSNPs($slug,$year, $fromDate, $toDate,$type),
        ];
    }

    /* ---------------- REGISTERED ---------------- */

    public function getTotalRegisteredSNPs($slug,$year, $fromDate, $toDate,$type)
    {
			$roleId=$this->getRoleIdBySnpSlug($slug);
			
			$totalSNP = DB::table('team_snp_scheme');

			if (!empty($fromDate) && !empty($toDate)) {

				$totalSNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					//dd($year);
					$totalSNP->whereYear('created_at', $year);
				}
			}

			$totalSNP = $totalSNP->count();
			//dd($totalSNP);


        $totalNP = DB::table('network_providers')->whereJsonContains('roles', $roleId)
			->whereNotIn('id', function ($query) {
                $query->select('network_provider_id')->from('team_snp_scheme');
            });
			
            if (!empty($fromDate) && !empty($toDate)) {

				$totalNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					$totalNP->whereYear('created_at', $year);
				}
			}
			
			$totalNP = $totalNP->count();

        return $totalSNP + $totalNP;
    }

    /* ---------------- PENDING ---------------- */

    public function getTotalPendingSNPs($slug,$year, $fromDate, $toDate,$type)
    {
        $roleId=$this->getRoleIdBySnpSlug($slug);

	    $totalSNP = DB::table('team_snp_scheme')
            ->where('status', 1);
            if (!empty($fromDate) && !empty($toDate)) {

				$totalSNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					$totalSNP->whereYear('created_at', $year);
				}
			}
           $totalSNP = $totalSNP->count();

        $totalNP = DB::table('network_providers')
		    ->whereJsonContains('roles', $roleId)
            ->where('status', 1)
            ->whereNotIn('id', function ($query) {
                $query->select('network_provider_id')->from('team_snp_scheme');
            });
			
           if (!empty($fromDate) && !empty($toDate)) {

				$totalNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					$totalNP->whereYear('created_at', $year);
				}
			}
			
			$totalNP = $totalNP->count();

        return $totalSNP + $totalNP;
    }


    /* ---------------- VERIFIED ---------------- */

    public function getTotalVerifiedSNPs($slug,$year, $fromDate, $toDate,$type)
    {
		$roleId=$this->getRoleIdBySnpSlug($slug);
		
        $totalSNP = DB::table('team_snp_scheme')->where('status', 2);
			
            if (!empty($fromDate) && !empty($toDate)) {

				$totalSNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					$totalSNP->whereYear('created_at', $year);
				}
			}

			$totalSNP = $totalSNP->count();

        $totalNP = DB::table('network_providers')->whereJsonContains('roles', $roleId)->where('status', 2)
            ->whereNotIn('id', function ($query) {
                $query->select('network_provider_id')->from('team_snp_scheme');
            });
			
            if (!empty($fromDate) && !empty($toDate)) {

				$totalNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					$totalNP->whereYear('created_at', $year);
				}
			}
			$totalNP = $totalNP->count();

        return $totalSNP + $totalNP;
    }


    /* ---------------- REJECTED ---------------- */

    public function getTotalRejectedSNPs($slug,$year, $fromDate, $toDate,$type)
    {
		$roleId=$this->getRoleIdBySnpSlug($slug);
		
        $totalSNP = DB::table('team_snp_scheme')->where('status', 3);
		
            if (!empty($fromDate) && !empty($toDate)) {
				$totalSNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					$totalSNP->whereYear('created_at', $year);
				}
			}

		$totalSNP = $totalSNP->count();

        $totalNP = DB::table('network_providers')->whereJsonContains('roles', $roleId)->where('status', 3)
            ->whereNotIn('id', function ($query) {
                $query->select('network_provider_id')->from('team_snp_scheme');
            });
			
            if (!empty($fromDate) && !empty($toDate)) {

				$totalNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					$totalNP->whereYear('created_at', $year);
				}
			}
			
			$totalNP = $totalNP->count();


        return $totalSNP + $totalNP;
    }

    /* ---------------- REVERTED ---------------- */

    public function getTotalRevertedSNPs($slug,$year, $fromDate, $toDate,$type)
    {
		$roleId=$this->getRoleIdBySnpSlug($slug);
		
        $totalSNP = DB::table('team_snp_scheme')->where('status', 4);
		
            if (!empty($fromDate) && !empty($toDate)) {
				$totalSNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {

				if ($type != 1) {
					$totalSNP->whereYear('created_at', $year);
				}
			}

		$totalSNP = $totalSNP->count();

        $totalNP = DB::table('network_providers')->whereJsonContains('roles', $roleId)->where('status', 4)
            ->whereNotIn('id', function ($query) {
                $query->select('network_provider_id')->from('team_snp_scheme');
            });
			
			
             if (!empty($fromDate) && !empty($toDate)) {
				$totalNP->whereBetween('created_at', [
					Carbon::parse($fromDate)->startOfDay(),
					Carbon::parse($toDate)->endOfDay(),
				]);

			} else {
				if ($type != 1) {
					$totalNP->whereYear('created_at', $year);
				}
			}
			
			$totalNP = $totalNP->count();

        return $totalSNP + $totalNP;
    }
	
	
	public function getRoleIdBySnpSlug($slug){
		return \DB::table('roles')->where('slug', $slug)->value('id');
	}
}
