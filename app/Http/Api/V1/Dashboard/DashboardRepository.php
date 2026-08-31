<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

use App\Enums\ReviewStatus;
use App\Enums\PaymentStatus;

class DashboardRepository 
{
    public static function getInstance()
    {
        return new DashboardRepository();
    }

    public function getApplicationCountByReviewTypes()
    {
        return \DB::select('select 
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications
                where payment_status = ?
                and is_sent_to_mib = 1
            ) as total_applications,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                inner join national_permission_production_details pd 
                    on npa.id = pd.national_permission_application_id
                where npa.payment_status = ?
                and pd.is_co_production = 0
                and npa.is_sent_to_mib = 1
            ) as total_filming_permission_for_live_action_shoot,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                inner join national_permission_production_details pd 
                    on npa.id = pd.national_permission_application_id
                where npa.payment_status = ?
                and pd.is_co_production = 1
                and npa.is_sent_to_mib = 1
            ) as total_official_coproduction_live_action,
            (
                select 
                lpad(count(*),2,"0") 
                from national_permission_applications
                where mib_review_status = ?
                and is_sent_to_mib = 1
            ) as approved_applications,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                inner join national_permission_production_details pd 
                    on npa.id = pd.national_permission_application_id
                where npa.mib_review_status = ?
                and pd.is_co_production = 0
                and npa.is_sent_to_mib = 1
            ) as total_approved_filming_permission_for_live_action_shoot,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                inner join national_permission_production_details pd 
                    on npa.id = pd.national_permission_application_id
                where npa.mib_review_status = ?
                and pd.is_co_production = 1
                and npa.is_sent_to_mib = 1
            ) as total_approved_official_coproduction_live_action,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications
                where mib_review_status = ?
                and is_sent_to_mib = 1
            ) as rejected_applications,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                inner join national_permission_production_details pd 
                    on npa.id = pd.national_permission_application_id
                where npa.mib_review_status = ?
                and pd.is_co_production = 0
                and npa.is_sent_to_mib = 1
            ) as total_rejected_filming_permission_for_live_action_shoot,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                inner join national_permission_production_details pd 
                    on npa.id = pd.national_permission_application_id
                where npa.mib_review_status = ?
                and pd.is_co_production = 1
                and npa.is_sent_to_mib = 1
            ) as total_rejected_official_coproduction_live_action,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications
                where mib_review_status in(?,?)
                and is_sent_to_mib = 1
            ) as inprogress_applications,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                inner join national_permission_production_details pd 
                    on npa.id = pd.national_permission_application_id
                where npa.mib_review_status in(?,?)
                and pd.is_co_production = 0
                and npa.is_sent_to_mib = 1
            ) as total_inprogress_filming_permission_for_live_action_shoot,
            (
                select 
                    lpad(count(*),2,"0") 
                from national_permission_applications npa
                inner join national_permission_production_details pd 
                    on npa.id = pd.national_permission_application_id
                where npa.mib_review_status in(?,?)
                and pd.is_co_production = 1
                and npa.is_sent_to_mib = 1
            ) as total_inprogress_official_coproduction_live_action,
            (
                select lpad(0,2,"0") 
            ) as total_animation_application,
            (
                select lpad(0,2,"0") 
            ) as total_approved_animation_application,
            (
                select lpad(0,2,"0") 
            ) as total_rejected_animation_application,
            (
                select lpad(0,2,"0") 
            ) as total_inprogress_animation_application', [
            PaymentStatus::Success->value, 
            PaymentStatus::Success->value, 
            PaymentStatus::Success->value, 
            ReviewStatus::Approve->value, 
            ReviewStatus::Approve->value, 
            ReviewStatus::Approve->value, 
            ReviewStatus::Reject->value, 
            ReviewStatus::Reject->value, 
            ReviewStatus::Reject->value, 
            ReviewStatus::InProgress->value, 
            ReviewStatus::Pending->value, 
            ReviewStatus::InProgress->value, 
            ReviewStatus::Pending->value, 
            ReviewStatus::InProgress->value, 
            ReviewStatus::Pending->value
        ]);
    }
}