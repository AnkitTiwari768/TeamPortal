<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use App\Traits\HasResponses;
use App\Web\Claim\ClaimReviewStatus as ReviewStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BnpWorkflowController
{
    use HasResponses;

    public function store(BnpWorkflowRequest $request, StoreBnpWorkflowAction $action): JsonResponse
    {
        $dto = $request->toDto();

        $action->execute($dto);

        $message = match ($dto->action) {
            ReviewStatus::APPROVED->value => __('application.approve_success'),
            ReviewStatus::REJECTED->value => __('Rejected successfully'),
            ReviewStatus::REVERTED->value => __('Reverted successfully'),
            ReviewStatus::SUBMITTED->value => __('application.submit_success'),
            ReviewStatus::FORWARDED->value => __('application.approve_success')
        };

        return $this->success(message: $message);
    }
}
