<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use App\Utils\UuidGenerator;
use App\Traits\HasCreateSubject;
use App\Web\AapleIntegration\AapleApplicationStatus;
use App\Web\AapleIntegration\AapleIntegrationConstant;
use App\Web\AapleIntegration\AapleIntegrationService;
use App\Web\ServiceApplication\ReviewStatus;
use App\Web\Timeline\TimelineService;
use Illuminate\Support\Facades\DB;
use App\Web\Sms\SmsTrigger;
use Carbon\Carbon;

class RejectApplicationAction
{
    use HasCreateSubject, SmsTrigger;

    public function __construct(
        private ApplicationWorkflowService $workflowService,
        private AapleIntegrationService $integrationService
    ) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {
            $userId = auth()->user()->id;
            $workflow = $this->workflowService->getUserWorkflow($userId);
            $now = Carbon::now();
            DB::table('rts_services')
                ->where('id', $data['application_id'])
                ->update([
                    'status' => ReviewStatus::Reject->value,
                    'review_status' => ReviewStatus::Reject->value,
                    'review_status_updated_at' => Carbon::now(),
                    'review_status_updated_by' => $userId,
                    'appeal_applicable_date' => date('Y-m-d'),
                ]);

            DB::table('service_workflow_logs')
                ->insert([
                    'id' => UuidGenerator::uuid7(),
                    'rts_service_id' => $data['application_id'],
                    'workflow_level' => $workflow->level,
                    'workflow_id' => $workflow->id,
                    'status' => ReviewStatus::Reject->value,
                    'comments' => $data['comments'] ?? null,
                    'created_at' => $now,
                    'created_by' => $userId
                ]);

            TimelineService::addApprovalDocument(
                serviceId: $data['application_id'],
                subject: $this->createSubject(ReviewStatus::Reject->value),
                comment: $data['comments'] ?? null,
                status: ReviewStatus::getName(ReviewStatus::Reject->value)
            );

            $application = DB::table('rts_services')->where('id', $data['application_id'])->first();

            $this->sendApplicationRejectedAlert($application->application_number, $application->mobile);
        });
    }
}
