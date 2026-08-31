<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

use App\Http\Controllers\ApiController;

class DashboardController extends ApiController
{
    public function __construct(private DashboardService $dashboardService) {}

    public function getApplicationTypes()
    {
        $responseData = $this->dashboardService->getApplicationTypes();
        return $this->success($responseData);
    }

    public function getApplicationCountByReviewTypes()
    {
        $responseData = $this->dashboardService->getApplicationCountByReviewTypes();
        return $this->success($responseData);
    }
	
	/*public function getRailwayApplicationByStatus(){
		$responseData = $this->dashboardService->getRailwayApplicationByStatus();
        return $this->success($responseData);
	}*/
}