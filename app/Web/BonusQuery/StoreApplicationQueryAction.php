<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use App\Utils\UuidGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Web\Timeline\TimelineService; 
use App\Web\Sms\SmsTrigger; 

class StoreApplicationQueryAction
{
    use SmsTrigger;
    public function execute(StoreApplicationQueryDto $dto)
    {  
        // dd($dto);
        return DB::transaction(function () use ($dto) {

            $uploadedFileIds = $dto->file_upload_ids ?? [];

            DB::table('bonus_queries')->insert([
                'id' => UuidGenerator::uuid7(),
                'msme_id' => $dto->msme_id,
                'claim_type_id' => $dto->claim_type_id,
                'query_number' => rand(1000, 9999) . '-' . date('dmY'),
                'upload_id' => !empty($uploadedFileIds) ? json_encode($uploadedFileIds) : null,
                'status' => QueryStatus::Open->value,
                'remark' => $dto->remarks,
                'raised_at' => now(),
                'raised_by' => auth()->id(),
            ]);


      

            $query= DB::table('team_msme_schemes')->where('id', $dto->msme_id)->first();
            //dd($query);

            $this->sendQueryAlertToApplicant($query); //Trait

             TimelineService::addApprovalDocument($dto->msme_id,'Bonus claim has been raised by '.auth()->user()->entrepreneur_name,$dto->remarks?? null,'3');

        });
    } 
}
