<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use Illuminate\Support\Facades\DB;

class MsmeTracker
{
    /**
     * Search in team_msme_schemes using dynamic criteria.
     */
    public function check(array $criteria, string $tabKey): array
    {
        $searchValue = reset($criteria);
        if (empty($searchValue)) {
            return [
                'found' => false,
                'status_key' => 'not_found',
                'message' => 'Please enter a search value.'
            ];
        }

        // Fetch all searchable fields for this module dynamically
        $searchableFields = DB::table('status_tab_fields as f')
            ->join('status_tabs as t', 'f.status_tab_id', '=', 't.id')
            ->where('t.tab_key', $tabKey)
            ->where('f.is_searchable', 1)
            ->pluck('f.field_key')
            ->toArray();

        // Ensure udyam_no and mobile are always searchable as per requirements
        $searchableFields = array_unique(array_merge($searchableFields, ['udyam_no', 'mobile']));

        $query = DB::table('team_msme_schemes');

        $query->where(function ($q) use ($searchValue, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'like', "%$searchValue%");
            }
        });

        $record = $query->orderByDesc('created_at')->first();

        if (!$record) {
            return [
                'found' => false,
                'status_key' => 'not_found',
                'message' => 'No MSME application found for the provided details.'
            ];
        }

        // Determine status key for timeline mapping
        $statusKey = 'pending';
        if (isset($record->status)) {
            if ($record->status == 1) $statusKey = 'approve';
            elseif ($record->status == 3) $statusKey = 'approve'; // From status_filter
            elseif ($record->status == 4) $statusKey = 'reject';
            elseif ($record->status == 5) $statusKey = 'revert';
        }

        return [
            'found'        => true,
            'status_key'   => $statusKey,
            'message'      => 'This MSE is already registered on TEAM Portal.',
            'badge_class'  => ($statusKey === 'approve') ? 'bg-success' : 'bg-info',
            'badge_icon'   => ($statusKey === 'approve') ? 'fa-check-circle' : 'fa-info-circle',
            'record'       => $record,
        ];
    }
}
