<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use App\Traits\HasResponses;
use App\Web\ServiceApplication\ReviewStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationWorkflowController
{
    use HasResponses;

    public function store(ApplicationWorkflowRequest $request, StoreApplicationWorkflowAction $action): JsonResponse
    {
        $dto = $request->toDto();

        $action->execute($dto);

        $message = match ($dto->action) {
            ReviewStatus::Approve->value => __('application.approve_success'),
            ReviewStatus::Forward->value => __('application.forward_success'),
            ReviewStatus::Reject->value => __('application.reject_success'),
            ReviewStatus::Revert->value => __('application.revert_success'),
            ReviewStatus::Submit->value => __('application.submit_success')
        };

        return $this->success(message: $message);
    }

    public function publish(Request $request, PublishApplicationAction $action)
    {
        $validated = $request->validate(
            [
                'application_id' => 'required|exists:rts_services,id',
                'comments'       => 'nullable',
            ],
            [
                'application_id.required' => __('validation.application_id.required'),
                'application_id.exists'   => __('validation.application_id.exists'),
            ]
        );

        $action->execute($validated);

        return $this->success(message: __('application.publish_success'));
    }

    public function reject(Request $request, RejectApplicationAction $action)
    {
        $validated = $request->validate(
            [
                'application_id' => 'required|exists:rts_services,id',
                'comments'       => 'nullable',
            ],
            [
                'application_id.required' => __('validation.application_id.required'),
                'application_id.exists'   => __('validation.application_id.exists'),
            ]
        );

        $action->execute($validated);

        return $this->success(message: __('application.reject_success'));
    }

    public function sendForApproval(Request $request, PublishApplicationAction $action)
    {
        $validated = $request->validate(
            [
                'application_id' => 'required|exists:rts_services,id',
                'service_id' => 'required|exists:rts_service_categories,id',
                'comments'       => 'nullable',
            ],
            [
                'application_id.required' => __('validation.application_id.required'),
                'application_id.exists'   => __('validation.application_id.exists'),
            ]
        );

        $action->sendApprovalRequest($validated);

        return $this->success(message: __('application.publish_success'));
    }
}
