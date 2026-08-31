<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\Support\Facades\DB;

class DeleteWorkshopAction
{
    public function execute(string $id): array
    {
        $workshop = Workshop::find($id);

        // Workshop not found OR already executed OR status is not null
        if (
            !$workshop ||
            $workshop->is_executed_workshop == 1 ||
            !is_null($workshop->status)
        ) {
            return [
                'status' => false,
                'message' => __('workshop.cannot_delete_workshop'),
            ];
        }

        // Delete uploaded files
        if (!empty($workshop->uploaded_ids)) {
            DB::table('file_uploads')
                ->whereIn('id', explode(',', $workshop->uploaded_ids))
                ->delete();
        }

        $workshop->delete();

        return [
            'status' => true,
            'message' => __('workshop.event_deleted_success'),
        ];
    }
}