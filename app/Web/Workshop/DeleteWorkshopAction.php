<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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

        // --- NEW: Check 48-hour restriction based on schedules ---
        if (!empty($workshop->schedules)) {
            // Assume schedules is a JSON array; take the first schedule
            $schedules = is_array($workshop->schedules) ? $workshop->schedules : json_decode($workshop->schedules, true);
            if (!empty($schedules) && isset($schedules[0]['start_date'], $schedules[0]['start_time'])) {
                $first = $schedules[0];
                // Parse date and time (format: day-month-year, hour:minute)
                $startDateTime = Carbon::createFromFormat('d-m-Y H:i', $first['start_date'] . ' ' . $first['start_time']);
                $now = Carbon::now();

                // 48 hours before the event start
                $threshold = $startDateTime->copy()->subHours(48);

                if ($now->greaterThanOrEqualTo($threshold)) {
                    return [
                        'status' => false,
                        'message' => __('workshop.cannot_delete_within_48_hours'),
                    ];
                }
            }
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