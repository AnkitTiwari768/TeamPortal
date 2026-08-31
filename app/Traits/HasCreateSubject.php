<?php

declare(strict_types=1);

namespace App\Traits;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;

trait HasCreateSubject
{
    protected function createSubject(mixed $action, $revertTo = NULL)
    {
        $user = auth()->user();
        $userName = trim($user->first_name . ' ' . $user->last_name);
        $msg='';
        if($revertTo != NULL){
            $msg = 'to '.$revertTo;
        }

        $userRole = DB::table('user_roles')
            ->select('roles.name as role_name')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_id', $user->id)
            ->first()
            ?->role_name ?? 'Unknown Role';

        return match ((int) $action) {
            ClaimReviewStatus::APPROVED->value => 'Application has been approved by ' . $userName . '(' . $userRole . ')',
            ClaimReviewStatus::REJECTED->value => 'Application has been rejected by ' . $userName . '(' . $userRole . ')',
            ClaimReviewStatus::FORWARDED->value => 'Application has been approved by ' . $userName . '(' . $userRole . ')',
            ClaimReviewStatus::REVERTED->value => 'Application has been reverted by ' . $userName . '(' . $userRole . ')'.$msg,
            ClaimReviewStatus::SUBMITTED->value => 'Application has been submitted by ' . $userName . '(' . $userRole . ')',
             ClaimReviewStatus::PAYMENT_COMPLETED->value => 'Application has been payment by ' . $userName . '(' . $userRole . ')',
        };
    }
}
