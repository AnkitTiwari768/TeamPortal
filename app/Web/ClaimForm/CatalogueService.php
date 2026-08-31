<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use App\Http\Services\ApiService;
use App\Http\Services\CommonService;
use App\Traits\HasFileUpload;
use App\Traits\DataTable;
use App\Web\Msme\MsmeResource;
use DB;
use App\Web\BonusQuery\QueryStatus;

class CatalogueService extends ApiService
{
    use DataTable, HasFileUpload;

    protected array $columns = [
        1 => 'team_id',
        2 => 'udyam_no',
        3 => 'mobile',
        4 => 'email',
        5 => 'entrepreneur_name',
        6 => 'enterprise_name',
        7 => 'organisation_type',
        8 => 'created_at'
    ];

    public function catalogueReadyMSMEList($is_uploaded)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'claims.bonus_amount',
                'bq.status as bonus_query_status',
                DB::raw("
                CASE bq.status
                WHEN " . QueryStatus::Open->value . " THEN 
                    '<span class=\"badge bg-primary\">Open</span>'

                 WHEN " . QueryStatus::Pending->value . " THEN 
                    '<span class=\"badge bg-warning\">In-Progress</span>'

                WHEN " . QueryStatus::Closed->value . " THEN 
                    '<span class=\"badge bg-secondary\">Closed</span>'

                WHEN " . QueryStatus::Accept->value . " THEN 
                    '<span class=\"badge bg-success\">Accepted</span>'

                WHEN " . QueryStatus::Reject->value . " THEN 
                    '<span class=\"badge bg-danger\">Rejected</span>'

                ELSE '<span class=\"badge bg-secondary\">NA</span>'
                        END AS bonus_query_status_name
            "),
                DB::raw("
                CASE
                  WHEN claims.bonus_amount IS NULL OR claims.bonus_amount <= 0 THEN 0
                    WHEN bq.status IN (
                        " . QueryStatus::Open->value . ",
                        " . QueryStatus::Pending->value . ",
                        " . QueryStatus::Closed->value . ",
                        " . QueryStatus::Accept->value . "
                    )
                    THEN 1 ELSE 0
                END AS disable_bonus_button
            "),
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->leftjoin('states as s', 's.id', '=', 'ms.state_id')
            ->leftJoin('claims', function ($join) {
                $join->on('claims.team_registration_id', '=', 'ms.team_id')
                    ->where('claims.bonus_amount', '>', 0);
            })
            ->leftJoin(DB::raw("
            (
                SELECT bq1.*
                FROM bonus_queries bq1
                INNER JOIN (
                    SELECT msme_id, MAX(raised_at) AS latest
                    FROM bonus_queries
                    GROUP BY msme_id
                ) bq2
                ON bq1.msme_id = bq2.msme_id
                AND bq1.raised_at = bq2.latest
            ) AS bq
        "), 'bq.msme_id', '=', 'ms.id')

            ->where('tsm.status', 1);

        if (hasRole('snp')) {
            $query->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
                ->where('tss.user_id', (string) AuthId());
        }

        if (is_null($is_uploaded)) {
            $query->whereNull('ms.is_uploaded');
        } else {
            $query->where('ms.is_uploaded', $is_uploaded);
        }


        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('ms.team_id', 'like', "%$search%")
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%");
            });
        }

        $query->orderBy($order, $dir);

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }

    public function catalogueRestrictedMSMEList($is_uploaded)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.major_activity',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'claims.bonus_amount',
                'bq.status as bonus_query_status',
                DB::raw("
                CASE bq.status
                WHEN " . QueryStatus::Open->value . " THEN 
                    '<span class=\"badge bg-primary\">Open</span>'

                 WHEN " . QueryStatus::Pending->value . " THEN 
                    '<span class=\"badge bg-warning\">In-Progress</span>'

                WHEN " . QueryStatus::Closed->value . " THEN 
                    '<span class=\"badge bg-secondary\">Closed</span>'

                WHEN " . QueryStatus::Accept->value . " THEN 
                    '<span class=\"badge bg-success\">Accepted</span>'

                WHEN " . QueryStatus::Reject->value . " THEN 
                    '<span class=\"badge bg-danger\">Rejected</span>'

                ELSE '<span class=\"badge bg-secondary\">NA</span>'
                        END AS bonus_query_status_name
            "),
                DB::raw("
                CASE
                  WHEN claims.bonus_amount IS NULL OR claims.bonus_amount <= 0 THEN 0
                    WHEN bq.status IN (
                        " . QueryStatus::Open->value . ",
                        " . QueryStatus::Pending->value . ",
                        " . QueryStatus::Closed->value . ",
                        " . QueryStatus::Accept->value . "
                    )
                    THEN 1 ELSE 0
                END AS disable_bonus_button
            "),
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->leftjoin('states as s', 's.id', '=', 'ms.state_id')
            ->leftJoin('claims', function ($join) {
                $join->on('claims.team_registration_id', '=', 'ms.team_id')
                    ->where('claims.bonus_amount', '>', 0);
            })
            ->leftJoin(DB::raw("
            (
                SELECT bq1.*
                FROM bonus_queries bq1
                INNER JOIN (
                    SELECT msme_id, MAX(raised_at) AS latest
                    FROM bonus_queries
                    GROUP BY msme_id
                ) bq2
                ON bq1.msme_id = bq2.msme_id
                AND bq1.raised_at = bq2.latest
            ) AS bq
        "), 'bq.msme_id', '=', 'ms.id')

            ->where('tsm.status', 1);


        $query->where(function ($query) {
            return $query
                ->whereIn(
                    DB::raw('LOWER(ms.major_activity)'),
                    ['trading']
                )
                ->orWhereIn(
                    DB::raw('LOWER(ms.msme_classification)'),
                    ['medium']
                );
        });




        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('ms.team_id', 'like', "%$search%")
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%");
            });
        }

        $query->orderBy($order, $dir);

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }



    public function deleteCatalogue($payload)
    {

        if ($payload['document_type'] == 'upload') {
            $path = "app/" . config('upload.catalogue_path');
        }
        $this->deleteFile($path, $payload['id']);
        return true;
    }

    public function updateCatalogue($payload)
    {
        $updateData = [
            'is_uploaded' => 1,
            'catalogue_document' => $payload['catalogue'] ?? null,
            'catalogue_document_original_name' => isset($payload['catalogue'])
                ? self::originalName($payload['catalogue']) : null,
        ];

        DB::table('team_msme_schemes')->where('id', $payload['msme_id'])->update($updateData);
        return true;
    }
}
