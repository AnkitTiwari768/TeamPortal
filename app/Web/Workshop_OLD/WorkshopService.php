<?php

declare(strict_types=1);

namespace App\Web\Workshop;


use App\Core\BaseService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Web\Workshop\WorkshopStatus;

class WorkshopService extends BaseService
{
    public function getEventData(string $eventId)
    {
        $row = DB::table('workshops as w')
            ->leftJoin('workshop_expenses as we', 'we.workshop_id', '=', 'w.id')
            ->select(
                'w.*',
                'we.number_of_participants',
                'we.expense_amount',
                'we.nsic_fees',
                'we.is_tds_applicable',
                'we.tds_percentage',
                'we.tds_amount',
                'we.sanction_order_no',
                'we.sanction_order_date',
                'we.supporting_documents',
                'we.remarks as expense_remarks'
            )
            ->where('w.id', $eventId)
            ->first();

        if ($row) {
            if ($row->schedules) {
                $row->schedules = json_decode($row->schedules, true) ?: [];
            }

            if (!empty($row->event_for) && !is_array($row->event_for)) {
                $row->event_for = json_decode($row->event_for, true) ?: [];
            }

            $row->branch_office = $row->branch_offices_id ?? null;

            // Map executed workshop / expense details to form field names
            $row->no_of_participants = $row->number_of_participants ?? null;
            $row->nsic_fee = $row->nsic_fees ?? null;
            $row->tds_applicable = isset($row->is_tds_applicable) ? ($row->is_tds_applicable ? 'Yes' : 'No') : '';
            $row->sanction_order_number = $row->sanction_order_no ?? null;
            $supportingDoc = $row->supporting_documents ?? null;
            if (is_string($supportingDoc)) {
                $decoded = json_decode($supportingDoc, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $supportingDoc = $decoded;
                }
            }
            $row->supporting_document = $supportingDoc;
            $row->supporting_document_url = null;
            if ($row->supporting_document) {
                $fileUpload = DB::table('file_uploads')->where('id', $row->supporting_document)->first();
                if ($fileUpload) {
                    $row->supporting_document_url = asset('storage/app/' . $fileUpload->file_path . '/' . $fileUpload->file_system_name);
                } else {
                    $row->supporting_document_url = asset('storage/' . $row->supporting_document);
                }
            }
            $row->remarks = $row->expense_remarks ?? null;

            // Calculate net_amount
            $expense = (float) ($row->expense_amount ?? 0);
            $nsic = (float) ($row->nsic_fees ?? 0);
            $tdsPercent = (float) ($row->tds_percentage ?? 0);
            $tdsAmount = ($row->is_tds_applicable) ? ($expense * $tdsPercent / 100) : 0;
            $row->net_amount = $expense + $nsic - $tdsAmount;
        }

        return $row;
    }

    public function getUploadImageData($id)
    {
        $workshop = DB::table('workshops')->where('id', $id)->first();

        if (!$workshop || !$workshop->uploaded_ids) {
            return collect();
        }

        $ids = explode(',', $workshop->uploaded_ids);

        return \DB::table('file_uploads')->whereIn('id', $ids)->get();
    }

    public function cardData()
    {
        $msmeRoleId = DB::table('roles')->where('slug', 'msme')->value('id');
        return DB::table('workshops as w')
            //->leftJoin('roles as r', 'w.event_for', '=', 'r.id')
            ->leftJoin('roles as r', function ($join) {
                $join->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(r.id))");
            })
            ->leftJoin('states as s', DB::raw('w.state_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.id COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('file_uploads as fu', 'fu.id', '=', 'w.uploaded_ids')
            ->select('w.*', DB::raw('GROUP_CONCAT(r.name) as event_for_name'), 's.name as state_name', 'fu.file_path', 'fu.file_system_name')
            ->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(?))", [$msmeRoleId])
            ->groupBy('w.id')
            ->orderBy('w.created_at', 'desc')
            ->get()
            ->map(fn($item) => $this->formatWorkshop($item));
    }

    public function formatWorkshop($item)
    {
        $schedule = json_decode($item->schedules, true);

        if (is_string($schedule)) {
            $schedule = json_decode($schedule, true);
        }

        if (!empty($schedule) && is_array($schedule)) {

            $startDates = [];
            $endDates   = [];

            foreach ($schedule as $row) {
                $startDates[] = Carbon::createFromFormat('d-m-Y', $row['start_date']);
                $endDates[]   = Carbon::createFromFormat('d-m-Y', $row['end_date']);
            }

            $start = collect($startDates)->min();
            $end   = collect($endDates)->max();

            $item->start_date = $start->format('d-m-Y');
            $item->end_date   = $end->format('d-m-Y');
            $item->duration   = $start->diffInDays($end) + 1;
        }

        return $item;
    }

    /*public function getScheduleSummary($schedules)
    {
        $schedules = json_decode($schedules, true);

        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }

        if(empty($schedules)){
            return [
                'start_date'=>null,
                'end_date'=>null,
                'duration'=>0,
                'schedules'=>[]
            ];
        }

        $startDates = collect($schedules)->pluck('start_date');
        $endDates   = collect($schedules)->pluck('end_date');

        $start = Carbon::createFromFormat('d-m-Y', $startDates->min());
        $end   = Carbon::createFromFormat('d-m-Y', $endDates->max());

        return [
            'start_date'=>$start->format('d-m-Y'),
            'end_date'=>$end->format('d-m-Y'),
            'duration'=>$start->diffInDays($end)+1,
            'schedules'=>$schedules
        ];
    }*/

    public function getScheduleSummary($schedules)
    {
        $schedules = is_string($schedules) ? json_decode($schedules, true) : $schedules;

        if (empty($schedules)) {
            return [
                'start_date' => null,
                'end_date'   => null,
                'duration'   => 0,
                'schedules'  => []
            ];
        }

        $startDates = array_column($schedules, 'start_date');
        $endDates   = array_column($schedules, 'end_date');

        $start = Carbon::createFromFormat('d-m-Y', min($startDates));
        $end   = Carbon::createFromFormat('d-m-Y', max($endDates));

        return [
            'start_date' => $start->format('d-m-Y'),
            'end_date'   => $end->format('d-m-Y'),
            'duration'   => $start->diffInDays($end) + 1,
            'schedules'  => $schedules
        ];
    }


    public function getAttributeMap(array $codes): array
    {
        $attributes = DB::table('attributes')
            ->whereIn('code', $codes)
            ->where('status', 1)
            ->pluck('id', 'code');

        $values = DB::table('attribute_values')
            ->whereIn('attribute_id', $attributes->values())
            ->where('status', 1)
            ->get(['id', 'attribute_value']);

        return $values->pluck('attribute_value', 'id')->toArray();
    }

    public function getBranchOfficeName(?string $codes = null)
    {
        return DB::table('nsic_branch_offices')
            ->when($codes, function ($q) use ($codes) {
                $q->where('code', $codes);
            })
            ->where('status', 1)
            ->pluck('name', 'id')
            ->toArray();
    }


    public function getEventForRoles()
    {
        return DB::table('roles')
            ->where('can_view_workshop', true)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getOrganizerNames()
    {
        return DB::table('roles')
            ->where('is_organizer', true)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getNsicBranchOffices(?string $key = null)
    {
        $query = DB::table('nsic_branch_offices')
            ->where('status', 1);

        if ($key !== null) {
            return $query->where('id', $key)->value('name');
        }

        return $query->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getWorkshopDetails(string $id): ?array
    {
        $event = Workshop::find($id);
        if (!$event) {
            return null;
        }

        $roleIds = is_string($event->event_for) ? json_decode($event->event_for, true) : $event->event_for;
        $roleIds = is_array($roleIds) ? $roleIds : [];
        $event_for = DB::table('roles')->whereIn('id', $roleIds)->pluck('name')->implode(', ');

        $uploadedImage = $this->getUploadImageData($id);

        $org_name = DB::table('roles')
            ->where('id', $event->organiser_name)
            ->value('name');

        // Robust decoding of schedules if it's double-encoded or string
        $schedules = $event->schedules;
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }
        $event->schedules = is_array($schedules) ? $schedules : [];

        return [
            'event' => $event,
            'event_for' => $event_for,
            'uploadedImage' => $uploadedImage,
            'org_name' => $org_name,
        ];
    }

    public function getEcecutedWorkshopDetails(string $id): ?array
    {
        $event = Workshop::find($id);
        if (!$event) {
            return null;
        }

        $roleIds = is_string($event->event_for) ? json_decode($event->event_for, true) : $event->event_for;
        $roleIds = is_array($roleIds) ? $roleIds : [];
        $event_for = DB::table('roles')
            ->whereIn('id', $roleIds)
            ->pluck('name')
            ->implode(', ');

        $uploadedImage = $this->getUploadImageData($id);

        $org_name = DB::table('roles')
            ->where('id', $event->organiser_name)
            ->value('name');

        // Get workshop expense details
       $workshopExpense = DB::table('workshop_expenses')
            ->where('workshop_id', $id)
            ->first();

        if ($workshopExpense) {

            $documentIds = json_decode($workshopExpense->supporting_documents, true);
            $documentIds = is_array($documentIds)
                ? $documentIds
                : [$workshopExpense->supporting_documents];

            // Remove extra quotes and spaces
            $documentIds = array_map(function ($id) {
                return trim($id, "\"' ");
            }, $documentIds);
            $workshopExpense->supporting_documents = DB::table('file_uploads')
                ->whereIn('id', $documentIds)
                ->get();
                
            }
            
        // Robust decoding of schedules if it's double-encoded or string
        $schedules = $event->schedules;
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }
        $event->schedules = is_array($schedules) ? $schedules : [];

        return [
            'event'            => $event,
            'event_for'        => $event_for,
            'uploadedImage'    => $uploadedImage,
            'org_name'         => $org_name,
            'workshopExpense'  => $workshopExpense,
        ];
    }

    public function updateStatus(string $id, array $data): bool
    {
        $workshop = Workshop::find($id);
        if ($workshop) {
            $workshop->status = $data['status'];
            $workshop->remark = $data['remark'] ?? null;
            $workshop->status_updated_at = now();

            if ($workshop->status === WorkshopStatus::CANCELLED->value) {
                $workshop->is_executed_workshop = false;
            }

            return $workshop->save();
        }
        return false;
    }
}
