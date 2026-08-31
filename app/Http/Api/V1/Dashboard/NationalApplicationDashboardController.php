<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

use App\Http\Controllers\ApiController;

class NationalApplicationDashboardController extends ApiController 
{
    public function __construct(private NationalApplicationDashboardService $dashboardService) {}

    public function getApplicationCountByReviewTypes()
    {
        $responseData = $this->dashboardService->getApplicationCountByReviewTypes();
        return $this->success($responseData);
    }

    public function getProductionCategoriesBarChart()
    {
        $responseData = $this->dashboardService->getProductionCategoriesBarChart();
        return $this->success($responseData);
    }

    public function getPieChartDataByReviewStatuses()
    {
        $responseData = $this->dashboardService->getPieChartDataByReviewStatuses();
        return $this->success($responseData);
    }

    public function getApplicationTimeAnalysisChart()
    {  
        $responseData = $this->dashboardService->getApplicationTimeAnalysisChart();
        return $this->success($responseData);
    }
}