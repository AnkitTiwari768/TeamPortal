<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use App\Traits\HasCreateSubject;
use App\Web\ServiceApplication\ReviewStatus;
use App\Web\Timeline\TimelineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateApplicationQueryAction
{
    use HasCreateSubject;

    public function execute(array $data)
    {
        
        DB::transaction(function () use ($data) {
             $applicationId = $data['application_id'] ?? null;

            if (!$applicationId) {
                DB::table('rts_service_queries')
                    ->where('id', $data['query_id'])
                    ->update([
                        'status' => $data['action'],
                        'remark' => $data['remark']
                    ]);
            }

            $applicationId = $data['application_id'] ?? DB::table('rts_service_queries')
                ->where('id', $data['query_id'])
                ->value('rts_service_id');

            if (
                $applicationId && in_array(
                    (int) $data['action'],
                    [QueryStatus::Open->value, QueryStatus::Pending->value, QueryStatus::Closed->value]
                )
            ) {
                DB::table('rts_services')->where('id', $applicationId)->update([
                    'scruitny_status' => $data['action'],
                    'scruitny_status_updated_at' => Carbon::now(),
                    'scruitny_status_updated_by' => auth()->user()->id
                ]); 
            }

            if ((int) $data['action'] === QueryStatus::Closed->value) {
                TimelineService::addApprovalDocument(
                    serviceId: $data['application_id'],
                    subject: auth()->user()->full_name . ' has completed scruitny of the application',
                    comment: $dto?->comments ?? null,
                    status: 'Verified'
                );
            }

             if (
                $applicationId && in_array(
                    (int) $data['action'],
                    [QueryStatus::Accept->value, QueryStatus::Reject->value]
                )
            ) {
                TimelineService::addApprovalDocument(
                    serviceId: $applicationId,
                    subject: 'Query has been '.QueryStatus::getName((int) $data['action']).' by '. auth()->user()->full_name,
                    comment: $dto?->comments ?? null,
                    status: 'Verified'
                );
             }
        });
    }
}
