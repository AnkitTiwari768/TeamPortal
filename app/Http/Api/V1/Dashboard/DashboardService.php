<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

use App\Core\BaseService;
use App\Enums\ReviewStatus;

use App\Http\Api\V1\ApplicationType\ApplicationTypeService;

class DashboardService extends BaseService 
{
    public static function getInstance()
    {
        return new DashboardService();
    }

    public function getApplicationTypes()
    {
        return ApplicationTypeService::getInstance()->getApplicationTypes();
    }

    public function getApplicationCounts()
    {
        return ApplicationTypeService::getInstance()->getApplicationTypes();
    }

    public function getApplicationCountByReviewTypes()
    {
        $result = (DashboardRepository::getInstance())->getApplicationCountByReviewTypes();

        return $result[0] ?? [];
    }
	
	 public function getRailwayApplicationCountByReviewTypes()
    {
        $result = (DashboardRepository::getInstance())->getRailwayApplicationCountByReviewTypes();

        return $result[0] ?? [];
    }
	
	public function getCentralBoardData()
	{
		$data = \DB::table('railway_permission_applications as rpa')
				->select("rpa.*")
				->where('is_sent_to_railway_board',1)
				->where('is_domestic',0)
				->get()->toArray();
		//dd($data);
		$centralRailwayDashboardData = [];
		$totalApprove = 0;
		$totalPending = 0;
		$totalReject = 0;
		if(count($data) > 0){
			foreach($data as $row){
				
				if($row->railway_board_status == ReviewStatus::Approve->value ){
					$totalApprove++;
				}
				if($row->railway_board_status == ReviewStatus::Reject->value ){
					$totalReject++;
				}
				if($row->railway_board_status == ReviewStatus::Revert->value ||  $row->railway_board_status == ReviewStatus::Pending->value ||$row->railway_board_status == null){
					$totalPending++;
				}
				
			}
		}
		$centralRailwayDashboardData = [
			'total_application' => count($data)??0,
			'approved_application' => $totalApprove??0,
			'pending_application' => $totalPending??0,
			'rejected_application' => $totalReject??0,
			
		];
		return $centralRailwayDashboardData;
	}
	
	public function getZonalRailwayData($isDomestic=false)
	{
		$zonalRailwayDashboardData = [];
		$totalZonalApprove = 0;
		$totalZonalPending = 0;
		$totalZonalReject = 0;
		
		
		$zonaldata = \DB::table('railway_permission_applications as rpa')
				->join('railway_permission_zonal_officers as rpzo','rpzo.railway_permission_application_id','rpa.id')
				->select('rpa.*','rpzo.status as zonal_status','zonal_officer_id')
				->where('is_sent_to_zonal_officer',1);
		if($isDomestic == 1){
			$zonaldata = $zonaldata->where('is_domestic',1);
		}else{
			$zonaldata = $zonaldata->where('is_domestic',0);
		}	
		$zonaldata = $zonaldata->where('zonal_officer_id',AuthId())->get()->toArray();
		//dd($zonaldata);
		if(count($zonaldata) > 0){
			foreach($zonaldata as $row){
				if($row->zonal_status == ReviewStatus::Approve->value ){
					$totalZonalApprove++;
				}
				if($row->zonal_status == ReviewStatus::Reject->value ){
					$totalZonalReject++;
				}
				if( $row->zonal_status == ReviewStatus::Revert->value ||  $row->zonal_status == ReviewStatus::Pending->value ||$row->zonal_status == null){
					$totalZonalPending++;
				}
				
			}
		}
		$zonalRailwayDashboardData = [
			'total_application' => count($zonaldata)??0,
			'approved_application' => $totalZonalApprove??0,
			'pending_application' => $totalZonalPending??0,
			'rejected_application' => $totalZonalReject??0,
			
		];
		
		return $zonalRailwayDashboardData;
		
	}


    public function applicationTypes()
    {
      $query = \DB::table('modules AS m')
        ->select('*')
        ->whereIn('m.slug', [
            'filming-permission-for-live-action-shoot',
            'official-coproduction-live-action',
            'official-coproduction-live-animation-only'
        ]); 

       $result = $query->get()->map(function ($item) {
            return (array) $item;
        })->toArray();
       return $result;
    }


     public function getZonalWiseApplications($isDomestic=null){  
		$zoneQuery = \DB::table('railway_zones as rz')
		    ->leftJoin('users as u', 'rz.id', '=', 'u.zone_id')
		    ->leftJoin('railway_permission_zonal_officers as rpzo', 'rpzo.zonal_officer_id', '=', 'u.id')
		    ->selectRaw(
		        'rz.id, rz.name as zone_name, u.first_name as officer_name, 
		        rpzo.zonal_officer_id, count(rpzo.railway_permission_application_id) as total_application'
		    );

			if ($isDomestic != 2) {
			    $zoneQuery = $zoneQuery->leftJoin('railway_permission_applications as rpa', 'rpa.id', '=', 'rpzo.railway_permission_application_id');
			    $zoneQuery = $zoneQuery->where('rpa.is_domestic', $isDomestic);
			}

			$zoneQuery = $zoneQuery->groupBy('rz.id')->get()->toArray(); 

		    $zonalApplication= $this->getZonalApplicationStatus($isDomestic);

		    $mergedData = [];

			// Convert $a2 to an associative array indexed by zonal_officer_id for quick look-up
			$indexedA2 = [];
			foreach ($zonalApplication as $item) {
			    $indexedA2[$item['zonal_officer_id']] = $item;
			}
			foreach ($zoneQuery as $item) {
			    $zonal_officer_id = $item->zonal_officer_id;
			    if (isset($indexedA2[$zonal_officer_id])) {
			        $mergedData[] = (object)array_merge((array)$item, $indexedA2[$zonal_officer_id]);
			    } else {
			        $mergedData[] = [
		    		'zonal_officer_id'=>$item->zonal_officer_id,
		    		'zone_name'=>$item->zone_name,
		    		'zonal_officer'=>$item->officer_name,
		    		'total_application'=>$item->total_application,
		    		'approved_application'=>0,
		    		'pending_application'=>0,
		    		'rejected_application'=>0
		    		];
			    }
			}
 		  

        return $mergedData;
    }

    public function getZonalApplicationStatus($isDomestic){

    $zonalRailwayDashboardData = [];
	$zonaldata = \DB::table('railway_permission_zonal_officers as rpzo')
    ->select('rpzo.status as zonal_status', 'rpzo.zonal_officer_id');
    if ($isDomestic != 2) {
	    $zonaldata = $zonaldata->leftJoin('railway_permission_applications as rpa', 'rpa.id', '=', 'rpzo.railway_permission_application_id');
	    $zonaldata = $zonaldata->where('rpa.is_domestic', $isDomestic);
	}
   $zonaldata= $zonaldata->get()->groupBy('zonal_officer_id');

	foreach ($zonaldata as $zonal_officer_id => $applications) {
	    $totalZonalApprove = 0;
	    $totalZonalPending = 0;
	    $totalZonalReject = 0;

	    foreach ($applications as $row) {
	        if ($row->zonal_status == ReviewStatus::Approve->value) {
	            $totalZonalApprove++;
	        } elseif ($row->zonal_status == ReviewStatus::Reject->value) {
	            $totalZonalReject++;
	        } elseif (
	            $row->zonal_status == ReviewStatus::Revert->value ||
	            $row->zonal_status == ReviewStatus::Pending->value ||
	            $row->zonal_status == null
	        ) {
	            $totalZonalPending++;
	        }
	    }

	    $zonalRailwayDashboardData[] = [
	        'zonal_officer_id' => $zonal_officer_id,
	        'approved_application' => $totalZonalApprove,
	        'pending_application' => $totalZonalPending,
	        'rejected_application' => $totalZonalReject,
	    ];
	}
	
	return $zonalRailwayDashboardData; 

    }
}