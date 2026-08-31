<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

use App\Enums\ReviewStatus;
use App\Enums\PaymentStatus;
use DB;
use Carbon;
class NationalApplicationDashboardRepository 
{
    public function getApplicationCountByReviewTypes(?string $type = null)
    {
        $sql = null;

        if ($type === 'coproduction') 
        {
            $sql = 'select 
                (
                    select 
                        lpad(count(*),2,"0") 
                    from national_permission_applications npa
                    left join national_permission_production_details pd
                    on npa.id = pd.national_permission_application_id
                    where npa.review_status != :draft 
                    and pd.is_co_production = 1
                    and (pd.is_co_production_animation IS NULL OR pd.is_co_production_animation = 0)
                    and (pd.is_filming IS NULL OR pd.is_filming = 0)
                    
                ) as total_applications,
                (
                    select 
                    lpad(count(*),2,"0") 
                    from national_permission_applications npa
                    left join national_permission_production_details pd
                    on npa.id = pd.national_permission_application_id
                    where npa.review_status = :approved
                    and pd.is_co_production = 1
                    and (pd.is_co_production_animation IS NULL OR pd.is_co_production_animation = 0)
                    and (pd.is_filming IS NULL OR pd.is_filming = 0)
                ) as approved_applications,
                (
                    select 
                        lpad(count(*),2,"0") 
                    from national_permission_applications npa
                    left join national_permission_production_details pd
                    on npa.id = pd.national_permission_application_id
                    where npa.review_status = :rejected
                    and pd.is_co_production = 1
                    and (pd.is_co_production_animation IS NULL OR pd.is_co_production_animation = 0)
                    and (pd.is_filming IS NULL OR pd.is_filming = 0)
                ) as rejected_applications,
                (
                    select 
                        lpad(count(*),2,"0") 
                    from national_permission_applications npa
                    left join national_permission_production_details pd
                    on npa.id = pd.national_permission_application_id
                    where (
                        npa.review_status = :inprogress 
                        OR npa.review_status = 7 
                        OR npa.review_status = 2 
                        OR npa.review_status = 4
                        OR npa.review_status = 10
                    )
                    and pd.is_co_production = 1
                    and (pd.is_co_production_animation IS NULL OR pd.is_co_production_animation = 0)
                    and (pd.is_filming IS NULL OR pd.is_filming = 0)
                ) as inprogress_applications';    
        }
        elseif ($type === 'coproduction-animation')
        {
            $sql = 'select 
                (
                    select 
                        lpad(count(*),2,"0") 
                    from national_permission_applications npa
                    left join national_permission_production_details pd
                    on npa.id = pd.national_permission_application_id
                    where npa.review_status != :draft 
                    and pd.is_co_production_animation = 1
                    and (pd.is_co_production IS NULL OR pd.is_co_production = 0)
                    and (pd.is_filming IS NULL OR pd.is_filming = 0)
                    
                ) as total_applications,
                (
                    select 
                    lpad(count(*),2,"0") 
                    from national_permission_applications npa
                    left join national_permission_production_details pd
                    on npa.id = pd.national_permission_application_id
                    where npa.review_status = :approved
                    and pd.is_co_production_animation = 1
                    and (pd.is_co_production IS NULL OR pd.is_co_production = 0)
                    and (pd.is_filming IS NULL OR pd.is_filming = 0)
                ) as approved_applications,
                (
                    select 
                        lpad(count(*),2,"0") 
                    from national_permission_applications npa
                    left join national_permission_production_details pd
                    on npa.id = pd.national_permission_application_id
                    where npa.review_status = :rejected
                    and pd.is_co_production_animation = 1
                    and (pd.is_co_production IS NULL OR pd.is_co_production = 0)
                    and (pd.is_filming IS NULL OR pd.is_filming = 0)
                ) as rejected_applications,
                (
                    select 
                        lpad(count(*),2,"0") 
                    from national_permission_applications npa
                    left join national_permission_production_details pd
                    on npa.id = pd.national_permission_application_id
                    where (
                        npa.review_status = :inprogress 
                        OR npa.review_status = 7 
                        OR npa.review_status = 2 
                        OR npa.review_status = 4
                        OR npa.review_status = 10
                    )
                    and pd.is_co_production_animation = 1
                    and (pd.is_co_production IS NULL OR pd.is_co_production = 0)
                    and (pd.is_filming IS NULL OR pd.is_filming = 0)
                ) as inprogress_applications';
        }
        else 
        {
            $sql = 'select 
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                left join national_permission_production_details pd
                on npa.id = pd.national_permission_application_id
                where npa.review_status != :draft 
                and (pd.is_co_production_animation IS NULL OR pd.is_co_production_animation = 0)
                and (pd.is_co_production IS NULL OR pd.is_co_production = 0)
            ) as total_applications,
            (
                select 
                lpad(count(*),2,"0") 
                from national_permission_applications npa
                left join national_permission_production_details pd
                on npa.id = pd.national_permission_application_id
                where npa.review_status = :approved
                and (pd.is_co_production_animation IS NULL OR pd.is_co_production_animation = 0)
                and (pd.is_co_production IS NULL OR pd.is_co_production = 0)
            ) as approved_applications,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                left join national_permission_production_details pd
                on npa.id = pd.national_permission_application_id
                where npa.review_status = :rejected
                and (pd.is_co_production_animation IS NULL OR pd.is_co_production_animation = 0)
                and (pd.is_co_production IS NULL OR pd.is_co_production = 0)
            ) as rejected_applications,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                left join national_permission_production_details pd
                on npa.id = pd.national_permission_application_id
                where (
                        npa.review_status = :inprogress 
                        OR npa.review_status = 7 
                        OR npa.review_status = 2 
                        OR npa.review_status = 4
                        OR npa.review_status = 10
                    )
                and (pd.is_co_production_animation IS NULL OR pd.is_co_production_animation = 0)
                and (pd.is_co_production IS NULL OR pd.is_co_production = 0)
            ) as inprogress_applications';
        }

        if ($sql)
        {
            return DB::select($sql, [
                ReviewStatus::Draft->value, 
                ReviewStatus::Approve->value, 
                ReviewStatus::Reject->value, 
                ReviewStatus::InProgress->value, 
            ]);
        }

    }

    // public function getApplicationCountByProductionCategories()
    // {
    //     $applicationCountQuery = DB::table('national_permission_applications as npa')
    //         ->join('national_permission_production_details as pd', 'npa.id', '=', 'pd.national_permission_application_id')
    //         ->join('production_categories as pc', 'pc.id', '=', 'pd.production_category_id')
    //         ->whereNotNull('npa.review_status')
    //         ->groupBy('pd.production_category_id')
    //         ->select('pc.name', DB::raw('COUNT(*) as number_of_applications'));

    //     $defaultCountQuery = DB::table('production_categories')
    //                 ->where('status', config('constant.ACTIVE'))
    //                 ->whereNotIn('name', function($query) {
    //                     $query->select('pc.name')
    //                         ->from('national_permission_applications as npa')
    //                         ->join('national_permission_production_details as pd', 'npa.id', '=', 'pd.national_permission_application_id')
    //                         ->join('production_categories as pc', 'pc.id', '=', 'pd.production_category_id')
    //                         ->whereNotNull('npa.review_status');
    //                 })
    //                 ->select('name', DB::raw('0 as number_of_applications'));

    //     return $applicationCountQuery->unionAll($defaultCountQuery)->get();
    // }

    public function getApplicationCountByProductionCategories(?string $type = null,$financial_year = null)
    {
        $applicationCountQuery = DB::table('national_permission_applications as npa')
                                    ->join('national_permission_production_details as pd', 'npa.id', '=', 'pd.national_permission_application_id')
                                    ->join('production_categories as pc', 'pc.id', '=', 'pd.production_category_id');

        if ($type === 'mib')
        {
            $applicationCountQuery
                ->where('npa.is_sent_to_mib', true)
                ->whereNotNull('npa.mib_review_status');
        }
        else 
        {
            $applicationCountQuery->whereNotNull('npa.review_status')->where('npa.review_status', '!=', \App\Enums\ReviewStatus::Draft->value);
        }

        if ($financial_year) {
            $startYear = $financial_year - 1;
            $endYear = $financial_year;

            $startDate = Carbon\Carbon::create($startYear, 4, 1);
            $endDate = Carbon\Carbon::create($endYear, 3, 31)->endOfDay();

            $applicationCountQuery->whereBetween('npa.applied_at', [$startDate, $endDate]);
        }else{
            $currentDate = Carbon\Carbon::now();
            $startDate = Carbon\Carbon::create($currentDate->year, 4, 1);
            $endDate = Carbon\Carbon::create($currentDate->year+1, 3, 31)->endOfDay();
        } 
            
        $applicationCountQuery
            ->groupBy('pd.production_category_id')
            ->select('pc.name', DB::raw('COUNT(*) as number_of_applications'));

        $defaultCountQuery = DB::table('production_categories')
                    ->where('status', config('constant.ACTIVE'))
                    ->whereNotIn('name', function($query) use ($type,$financial_year, $startDate, $endDate) {
                        $query->select('pc.name')
                            ->from('national_permission_applications as npa')
                            ->join('national_permission_production_details as pd', 'npa.id', '=', 'pd.national_permission_application_id')
                            ->join('production_categories as pc', 'pc.id', '=', 'pd.production_category_id');
                        
                            if ($type === 'mib')
                            {
                                $query->whereNotNull('npa.mib_review_status');
                            }
                            else 
                            {
                                $query->whereNotNull('npa.review_status')->where('npa.review_status', '!=', \App\Enums\ReviewStatus::Draft->value);
                            }

                             if ($financial_year) {
                                $query->whereBetween('npa.applied_at', [$startDate, $endDate]);
                            }
                    })
                    ->select('name', DB::raw('0 as number_of_applications'));

        return $applicationCountQuery->unionAll($defaultCountQuery)->get();
    }

    public function getMibApplicationPercentageByReviewStatuses($financial_year = null)
    {   if ($financial_year) {
            $startYear = $financial_year - 1;
            $endYear = $financial_year;

            $startDate = Carbon\Carbon::create($startYear, 4, 1);
            $endDate = Carbon\Carbon::create($endYear, 3, 31)->endOfDay();
        }else{
            $currentDate = Carbon\Carbon::now();
            $startDate = Carbon\Carbon::create($currentDate->year, 4, 1);
            $endDate = Carbon\Carbon::create($currentDate->year+1, 3, 31)->endOfDay();
        } 

        $query = DB::table('national_permission_applications')
                ->selectRaw('COUNT(*) AS total_applications')
                ->selectRaw('(COUNT(CASE WHEN mib_review_status = ? THEN 1 END) / COUNT(*)) * 100 AS submitted_status_percentage', [ReviewStatus::Submitted->value])
                ->selectRaw('(COUNT(CASE WHEN mib_review_status = ? THEN 1 END) / COUNT(*)) * 100 AS inprogress_status_percentage', [ReviewStatus::InProgress->value])
                ->selectRaw('(COUNT(CASE WHEN mib_review_status = ? THEN 1 END) / COUNT(*)) * 100 AS approve_status_percentage', [ReviewStatus::Approve->value])
                ->selectRaw('(COUNT(CASE WHEN mib_review_status = ? THEN 1 END) / COUNT(*)) * 100 AS reject_status_percentage', [ReviewStatus::Reject->value])
                ->where('is_sent_to_mib', true)
                ->whereNotNull('mib_review_status')
                ->whereNotIn('mib_review_status', [
                    ReviewStatus::Revert->value,
                    ReviewStatus::Pending->value, 
                    ReviewStatus::Hold->value,
                    ReviewStatus::Draft->value
                ]);

            if ($financial_year) { 
                $query->whereBetween('applied_at', [$startDate, $endDate]);
            }

            return $query->first();
    }

    public function getFfoApplicationPercentageByReviewStatuses($financial_year = null)
    {
        if ($financial_year) {
            $startYear = $financial_year - 1;
            $endYear = $financial_year;

            $startDate = Carbon\Carbon::create($startYear, 4, 1);
            $endDate = Carbon\Carbon::create($endYear, 3, 31)->endOfDay();
        }else{
            $currentDate = Carbon\Carbon::now();
            $startDate = Carbon\Carbon::create($currentDate->year, 4, 1);
            $endDate = Carbon\Carbon::create($currentDate->year+1, 3, 31)->endOfDay();
        }

        $query = DB::table('national_permission_applications')
                ->selectRaw('COUNT(*) AS total_applications')
                ->selectRaw('(COUNT(CASE WHEN review_status = ? THEN 1 END) / COUNT(*)) * 100 AS submitted_status_percentage', [ReviewStatus::Submitted->value])
                ->selectRaw('(COUNT(CASE WHEN review_status = ? THEN 1 END) / COUNT(*)) * 100 AS inprogress_status_percentage', [ReviewStatus::InProgress->value])
                ->selectRaw('(COUNT(CASE WHEN review_status = ? THEN 1 END) / COUNT(*)) * 100 AS approve_status_percentage', [ReviewStatus::Approve->value])
                ->selectRaw('(COUNT(CASE WHEN review_status = ? THEN 1 END) / COUNT(*)) * 100 AS reject_status_percentage', [ReviewStatus::Reject->value])
                ->whereNotNull('review_status')
                ->whereNotIn('review_status', [
                    ReviewStatus::Revert->value,
                    ReviewStatus::Pending->value, 
                    ReviewStatus::Hold->value,
                    ReviewStatus::Draft->value
                ]);

        if ($financial_year) {
            $query->whereBetween('applied_at', [$startDate, $endDate]);
        }

        return $query->first();
    }

    public function getApplicationPercentageByReviewStatuses(?string $type = null, $year = null)
    {  
        if ($type === 'mib') 
        {
            return $this->getMibApplicationPercentageByReviewStatuses($year);
        }

        return $this->getFfoApplicationPercentageByReviewStatuses($year);
    }

    public function getApprovalTimeTakenCollection($financial_year = null)
    {   //dd($financial_year);
        if ($financial_year) {
            $startYear = $financial_year - 1;
            $endYear = $financial_year;

            $startDate = Carbon\Carbon::create($startYear, 4, 1);
            $endDate = Carbon\Carbon::create($endYear, 3, 31)->endOfDay();
        }else{
            $currentDate = Carbon\Carbon::now();
            $startDate = Carbon\Carbon::create($currentDate->year, 4, 1);
            $endDate = Carbon\Carbon::create($currentDate->year+1, 3, 31)->endOfDay();
        }

        $query = DB::table('national_permission_applications')
        ->select(
            DB::raw("
                CASE
                    WHEN net_approval_time_taken BETWEEN 1 AND 21 THEN 'A'
                    WHEN net_approval_time_taken BETWEEN 22 AND 30 THEN 'B'
                    WHEN net_approval_time_taken BETWEEN 31 AND 40 THEN 'C'
                    ELSE 'D'
                END AS slot,
                COUNT(*) as count
            ")
        )
        ->where('review_status', ReviewStatus::Approve->value)
        ->where('payment_status', PaymentStatus::Success->value)
        ->whereNotNull('net_approval_time_taken');


            if ($financial_year) {
                $query->whereBetween('applied_at', [$startDate, $endDate]);
            }
           $query->groupBy('slot');

        // Execute the query and return the results
        return $query->get()->toArray();
    }
}