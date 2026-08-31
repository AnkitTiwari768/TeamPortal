<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Traits\DataTable;

class Service
{
    use DataTable;

    /**
     * ── Helper Methods ───────────────────────────────────────────
     */

    public function getTabSettings(string $tabKey)
    {
        return DB::table('status_tabs')->where('tab_key', $tabKey)->first();
    }

    public function getTabBySlug(string $slug)
    {
        $tab = DB::table('status_tabs')
            ->where('slug', $slug)
            ->where('is_enabled', true)
            ->first();

        if ($tab) {
            $tab->searchableFields = $this->getTabFields($tab->id, true);
        }

        return $tab;
    }

    public function getTabFields(string $tabId, bool $searchableOnly = false)
    {
        $query = DB::table('status_tab_fields')->where('status_tab_id', $tabId);
        
        if ($searchableOnly) {
            $query->where('is_searchable', true);
        }

        // Note: is_active check would go here if column existed.
        // For now, is_searchable acts as the active flag for the search form.

        return $query->orderBy('sort_order', 'asc')->get();
    }

    public function getTabTimelines(string $tabId)
    {
        return DB::table('status_tab_timelines')->where('status_tab_id', $tabId)->where('is_enabled', true)->orderBy('sequence')->get();
    }

    /**
     * ── Public Logic ──────────────────────────────────────────────
     */

    public function resolve(string $tabKey, array $criteria, string $ip, string $userAgent): array
    {
        $tab = $this->getTabSettings($tabKey);

        if (!$tab || !$tab->is_enabled) {
            return ['status' => false, 'message' => 'Status Tab not found or disabled.'];
        }

        $trackerClass = $tab->tracker_class;
        if (!$trackerClass || !class_exists($trackerClass)) {
            return ['status' => false, 'message' => 'Tracker not configured.'];
        }

        $tracker = app($trackerClass);

        $trackerResult = $tracker->check($criteria, $tabKey);

        if (!$trackerResult['found']) {
            return ['status' => false, 'message' => $trackerResult['message']];
        }

        $record = $trackerResult['record'];
        $statusKey = $trackerResult['status_key'];

        // Dynamic Result Mapping: Only show fields enabled in Admin Popup Settings
        $displayData = [];
        $fields = DB::table('status_tab_fields')
            ->where('status_tab_id', $tab->id)
            ->where('is_visible_in_popup', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        foreach ($fields as $field) {
            $val = $record->{$field->field_key} ?? '—';
            $displayData[] = [
                'label' => $field->field_label, 
                'value' => $val,
                'sort_order' => $field->sort_order
            ];
        }

        // Add Timeline if enabled
        $timeline = [];
        if ($tab->is_timeline_enabled) {
            $timeline = $this->getTimelineStages($tab->id, $statusKey);
        }

        $primaryIdentifier = $this->extractPrimaryIdentifier($criteria);
        $this->logSearch($tab->id, $primaryIdentifier, $statusKey, $ip, $userAgent);

        return [
            'status' => true,
            'data' => [
                'status_key' => $statusKey,
                'message' => $trackerResult['message'] ?? 'Record Found.',
                'badge_class' => $trackerResult['badge_class'] ?? 'bg-success',
                'badge_icon' => $trackerResult['badge_icon'] ?? 'fa-check-circle',
                'display_data' => $displayData,
                'timeline' => $timeline,
                'tab' => [
                    'tab_key' => $tab->tab_key,
                    'label' => $tab->label,
                    'is_timeline_enabled' => $tab->is_timeline_enabled,
                    'is_remarks_enabled' => $tab->is_remarks_enabled,
                    'is_receipt_enabled' => $tab->is_receipt_enabled,
                ],
            ]
        ];
    }

    public function getEnabledModules(): array
    {
        $tabs = DB::table('status_tabs')->where('is_enabled', true)->orderBy('tab_order')->get();
        $results = [];

        foreach ($tabs as $tab) {
            $tab->searchableFields = $this->getTabFields($tab->id, true);
            $results[] = $tab;
        }

        return $results;
    }

    private function getTimelineStages(string $tabId, string $currentKey): array
    {
        $stages = $this->getTabTimelines($tabId);
        $hitCurrent = false;
        $timeline = [];

        foreach ($stages as $stage) {
            $isCurrent = ($stage->stage_key === $currentKey);
            
            $timeline[] = [
                'label' => $stage->stage_label,
                'completed' => !$hitCurrent && !$isCurrent,
                'current' => $isCurrent,
                'color' => $stage->status_color ?? 'success',
            ];

            if ($isCurrent) {
                $hitCurrent = true;
            }
        }

        return $timeline;
    }

    private function logSearch($tabId, $identifier, $status, $ip, $userAgent): void
    {
        DB::table('status_tab_search_logs')->insert([
            'id' => (string) Str::orderedUuid(),
            'status_tab_id' => $tabId,
            'identifier_hash' => hash('sha256', strtolower(trim($identifier))),
            'identifier_type' => 'dynamic',
            'result_status_key' => $status,
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 500),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function extractPrimaryIdentifier(array $criteria): string
    {
        foreach ($criteria as $val)
            if (!empty($val))
                return (string) $val;
        return 'unknown';
    }

    /**
     * ── Admin Logic ──────────────────────────────────────────────
     */

    public function getTabsForDatalist()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $query = DB::table('status_tabs');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%$search%")
                    ->orWhere('tab_key', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $data = $query->orderBy($order, $dir)->offset(($page - 1) * $limit)->limit($limit)->get();

        foreach ($data as $row) {
            $row->fields_count = DB::table('status_tab_fields')->where('status_tab_id', $row->id)->count();
            $row->search_logs_count = DB::table('status_tab_search_logs')->where('status_tab_id', $row->id)->count();
        }

        return [
            "draw" => intval(request()->input('draw')),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data,
        ];
    }

    public function getFieldsForDatalist($tabId)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams(columns: [
            0 => 'sort_order',
            1 => 'field_key',
            2 => 'field_label'
        ]);
        $query = DB::table('status_tab_fields')->where('status_tab_id', $tabId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('field_label', 'like', "%$search%")
                    ->orWhere('field_key', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $data = $query->orderBy($order, $dir)->offset(($page - 1) * $limit)->limit($limit)->get();

        return [
            "draw" => intval(request()->input('draw')),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data,
        ];
    }

    public function getTimelinesForDatalist($tabId)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams(columns: [
            0 => 'sequence',
            1 => 'stage_label'
        ]);
        $query = DB::table('status_tab_timelines')->where('status_tab_id', $tabId);

        if ($search) {
            $query->where('stage_label', 'like', "%$search%");
        }

        $total = $query->count();
        $data = $query->orderBy($order, $dir)->offset(($page - 1) * $limit)->limit($limit)->get();

        return [
            "draw" => intval(request()->input('draw')),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data,
        ];
    }

    public function getLogsForDatalist()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams(columns: [
            0 => 'created_at'
        ]);

        $query = DB::table('status_tab_search_logs as l')
            ->join('status_tabs as t', 'l.status_tab_id', '=', 't.id')
            ->select('l.*', 't.label as tab_label');

        $total = $query->count();
        $data = $query->orderBy($order, $dir)->offset(($page - 1) * $limit)->limit($limit)->get();

        return [
            "draw" => intval(request()->input('draw')),
            "recordsTotal" => $total,
            "recordsFiltered" => $total,
            "data" => $data,
        ];
    }

    public function findModule($id)
    {
        return DB::table('status_tabs')->where('id', $id)->first();
    }

    public function saveModule(array $data, string $id = null)
    {
        $payload = [
            'label' => $data['label'],
            'tab_key' => $data['tab_key'],
            'tracker_class' => $data['tracker_class'],
            'tab_order' => $data['tab_order'] ?? 0,
            'icon' => $data['icon'] ?? 'fa-search',
            'hint_text' => $data['hint_text'] ?? '',
            'is_enabled' => isset($data['is_enabled']) ? 1 : 0,
            'is_timeline_enabled' => isset($data['is_timeline_enabled']) ? 1 : 0,
            'is_remarks_enabled' => isset($data['is_remarks_enabled']) ? 1 : 0,
            'is_receipt_enabled' => isset($data['is_receipt_enabled']) ? 1 : 0,
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('status_tabs')->where('id', $id)->update($payload);
            return $id;
        }

        $payload['id'] = (string) Str::orderedUuid();
        $payload['created_at'] = now();
        DB::table('status_tabs')->insert($payload);
        return $payload['id'];
    }

    public function deleteModule($id): void
    {
        DB::table('status_tabs')->where('id', $id)->delete();
    }

    public function saveField(array $data, string $id = null)
    {
        $payload = [
            'status_tab_id' => $data['status_tab_id'],
            'field_key' => $data['field_key'],
            'field_label' => $data['field_label'],
            'field_type' => $data['field_type'] ?? 'text',
            'placeholder' => $data['placeholder'] ?? '',
            'sort_order' => $data['sort_order'] ?? 0,
            'is_searchable' => isset($data['is_searchable']) ? 1 : 0,
            'is_required' => isset($data['is_required']) ? 1 : 0,
            'is_visible_in_popup' => isset($data['is_visible_in_popup']) ? 1 : 0,
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('status_tab_fields')->where('id', $id)->update($payload);
            return $id;
        }

        $payload['id'] = (string) Str::orderedUuid();
        $payload['created_at'] = now();
        DB::table('status_tab_fields')->insert($payload);
        return $payload['id'];
    }

    public function deleteField($id): void
    {
        DB::table('status_tab_fields')->where('id', $id)->delete();
    }

    public function saveTimeline(array $data, string $id = null)
    {
        $payload = [
            'status_tab_id' => $data['status_tab_id'],
            'stage_key' => $data['stage_key'],
            'stage_label' => $data['stage_label'],
            'sequence' => $data['sequence'] ?? 0,
            'status_color' => $data['status_color'] ?? 'success',
            'is_enabled' => isset($data['is_enabled']) ? 1 : 0,
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('status_tab_timelines')->where('id', $id)->update($payload);
            return $id;
        }

        $payload['id'] = (string) Str::orderedUuid();
        $payload['created_at'] = now();
        DB::table('status_tab_timelines')->insert($payload);
        return $payload['id'];
    }

    public function deleteTimeline($id): void
    {
        DB::table('status_tab_timelines')->where('id', $id)->delete();
    }
}
