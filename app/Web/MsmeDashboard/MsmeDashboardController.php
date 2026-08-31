<?php

declare(strict_types=1);

namespace App\Web\MsmeDashboard;;

use App\Domain\Msme\MsmeDetailsAction;
use App\Domain\Msme\MsmeJourneyAction;
use App\Domain\Msme\SnpDetailsAction;
use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\DataTable;


final class MsmeDashboardController extends ClientController
{
	use DataTable;
	public function __construct() {}


	public function getData()
	{
		return view('dashboard.snp')->with('title', __('message.dashboard_list'));
	}

	public function myprofile(){
		$module_url = 'msme-my-profile';
		$data  = app(MsmeDetailsAction::class)->execute(authId());
		return view('msme.my_profile',compact('module_url','data'))->with('title', __('My profile'));
	}

	public function viewSnpDetails(){
		$module_url = 'view-snp-details-';
		$snpDetail  = app(SnpDetailsAction::class)->execute(authId());
		//dd($snpDetail);
		return view('msme.snp_details',compact('module_url','snpDetail'))->with('title', __('SNP Details'));
	}

	public function relevantSnp(){
		$module_url = 'relevant-snp';
		$title = "Relevant SNP";
		return view('msme.relevant-snp',compact('title','module_url'));
	}



}
