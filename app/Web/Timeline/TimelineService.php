<?php

declare(strict_types=1);

namespace App\Web\Timeline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimelineService
{
    public static function addApprovalDocument($serviceId, $subject, $comment, $status)
    {
        $insertData = [
            'id' => uuid(),
            'rts_service_id' => $serviceId,
            'subject' => $subject,
            'comment' => $comment,
            'status' => $status,
            'updated_at' => currentDateTime(),
            'updated_by' => AuthId(),
        ];
        return DB::table('rts_service_timeline')->insert($insertData);
    }


    public function createTimeline(array $data): void
    {
        DB::table('timelines')->insert([
            'id' => uuid(),
            'entity_id' => $data['entity_id'],
            'entity_type' => $data['entity_type'],
            'subject' => $data['subject'],
            'comment' => $data['comment'],
            'status' => $data['status'],
            'updated_at' => now(),
            'updated_by' => authId()
        ]);
    }


    // public function getTimelineHistory($appID)
    // {
    // 	$query =  \DB::table('rts_service_timeline as rst')
    // 			->select(
    //                 'rst.*',
    //                 \DB::raw("DATE_FORMAT(rst.updated_at, '%d-%m-%Y %H:%i:%s') as preview_updated_at")
    //             )
    // 			->where('rst.rts_service_id',$appID);

    // 	$query = $query->orderBy('updated_at','ASC'); 
    //     return $query->get();

    // }


    public function getTimelineHistory(string $entityId, ?string $entityType = null)
    {
        if (! $entityType) {
            return $this->getBatchTimelineHistories(batchId: $entityId);
        }

        return $this->getTimelineHistories($entityId);
    }

    public function getBatchTimelineHistories(string $batchId)
    {
        $query = DB::table('batch_timelines')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as created_at")
            )
            ->where('batch_id', $batchId);

        if (hasRole('ca')) {
            // $query->where('is_shown_to_ca', true);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getBatchTimelineHistoryDetails(string $batchTimelineId)
    {
        return DB::table('batch_timeline_details')->where('batch_timeline_id', $batchTimelineId)->get();
    }

    public function getTimelineHistories(string $entityId)
    {
        return DB::table('timelines')
            ->where('entity_id', $entityId)
            ->get();
    }
}
