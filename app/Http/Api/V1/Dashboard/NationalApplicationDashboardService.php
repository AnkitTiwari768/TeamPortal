<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

use App\Enums\ReviewStatus;
use App\Enums\PaymentStatus;
use DB;

class NationalApplicationDashboardService extends DashboardService 
{
    public static function getInstance(): NationalApplicationDashboardService
    {
        return new NationalApplicationDashboardService();
    }

    public function getApplicationCountByReviewTypes(?string $type = null)
    { 
        $result = (new NationalApplicationDashboardRepository())
                    ->getApplicationCountByReviewTypes($type);
        
        return $result[0];
    }

    public function getProductionCategoriesBarChart(?string $type,$year=null): ?array
    {

        $result = (new NationalApplicationDashboardRepository())
                        ->getApplicationCountByProductionCategories($type,$year);
        
        if ($result) 
        {
            return (new BarChart())
                        ->setResult($result->toArray())
                        ->setCategoryKey('name')
                        ->setValueKey('number_of_applications')
                        ->create();
        }

        return null;
    }

    public function getPieChartDataByReviewStatuses(?string $type = null,$year = null): ?array
    {
        $result = (new NationalApplicationDashboardRepository())
                    ->getApplicationPercentageByReviewStatuses($type, $year);

        if ($result) 
        {
            $data = [
                PieChart::create(name: 'Pending for action', value: (float) $result->submitted_status_percentage)
                    ->toArray(),

                PieChart::create(name: ReviewStatus::InProgress->getLabel(), value: (float) $result->inprogress_status_percentage)
                    ->toArray(),

                PieChart::create(name: ReviewStatus::Approve->getLabel(), value: (float) $result->approve_status_percentage)
                    ->toArray(),

                PieChart::create(name: ReviewStatus::Reject->getLabel(), value: (float) $result->reject_status_percentage)
                    ->toArray(),
            ];

            return [
                'data' => $data
            ];
        }

        return null;
    }

    public function getApplicationTimeAnalysisChart($type=null, $year =null)
    {
        $aprovalTimeTakenCollection = (new NationalApplicationDashboardRepository())
            ->getApprovalTimeTakenCollection($year);
        
        return [
            [
                'name' => '1-21',
                'y' =>  self::find_slot($aprovalTimeTakenCollection, 'A'),
            ],
            [
                'name' => '22-30',
                'y' =>  self::find_slot($aprovalTimeTakenCollection, 'B'),
            ],
            [
                'name' => '31-40',
                'y' =>  self::find_slot($aprovalTimeTakenCollection, 'C'),
            ],
            [
                'name' => '> 41',
                'y' =>  self::find_slot($aprovalTimeTakenCollection, 'D'),
            ]
        ];
    }

    public static function find_slot(array $collection, string $slot)
    {
        foreach ($collection as $collect) 
        {
            if ($collect->slot === $slot) return $collect->count;
        }

        return 0;
    }
}