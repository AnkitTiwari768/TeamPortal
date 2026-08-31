<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use App\Traits\DataTable;
use App\Utils\UuidGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Web\Timeline\TimelineService; 
use App\Web\Sms\SmsTrigger; 

class StoreQueryLogAction
{   
    use SmsTrigger;
    public function execute(StoreQueryLogDto $dto): void
    {  
        DB::transaction(function () use ($dto) {
            $user = auth()->user();
            //dd($dto);
            $queryLogData = [
                'id' => UuidGenerator::uuid7(),
                'rts_service_query_id' => $dto->query_id,
                'comments' => $dto->comments,
                'documents' => json_encode($dto->documents),
                'status' => QueryStatus::Pending->value,
                'replied_at' => Carbon::now(),
                'replied_by' => $user->id
            ];
           // dd($queryLogData);
            $queryData = [
                'status' => QueryStatus::Pending->value,
            ];

            DB::table('rts_service_query_logs')->insert($queryLogData);
            DB::table('rts_service_queries')->where('id', $dto->query_id)->update($queryData);
           $application= DB::table('rts_service_queries')->where('id', $dto->query_id)->first();

           $this->sendQueryReplyToApprovar($dto->query_id);
            TimelineService::addApprovalDocument($application->rts_service_id,'Query response by '.auth()->user()->full_name,$dto->comments?? null,'3');//application_id,subject,comment,status

        });
    }

    
}
