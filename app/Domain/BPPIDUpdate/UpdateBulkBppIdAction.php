<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateBulkBppIdAction
{
    public function bulkUpdateMsmeBpp(string $snpTeamId, array $records): array
    {

        $now = now();

        $snpId = $this->getSnpId($snpTeamId);

        if (!$snpId) {
            return [
                'success' => false,
                'message' => 'Invalid SNP Team ID',
                'mapped' => [],
                'unmapped' => collect($records)->map(fn ($record) => [
                    'udyam_no' => $record['udyam_no'],
                    'bpp_id'   => $record['bpp_id'],
                    'reason'   => 'Invalid SNP Team ID',
                ])->all(),
            ];
        }

        $udyamNos = collect($records)->pluck('udyam_no')->toArray();

        /*
        |--------------------------------------------------------------------------
        | GET MSME IDS + CURRENT BPP ID FROM UDYAM NO
        |--------------------------------------------------------------------------
        */
        $msmeMap = DB::table('team_msme_schemes')
            ->whereIn('udyam_no', $udyamNos)
            ->get(['id', 'udyam_no', 'bpp_id'])
            ->keyBy('udyam_no');

        // How many times each Udyam No. appears in this upload batch, so
        // mapped rows can be flagged as an in-file duplicate for the report.
        // This is reporting-only and does not change which value gets saved.
        $udyamOccurrences = collect($records)->countBy('udyam_no');

        return DB::transaction(function () use ($records, $snpId, $msmeMap, $now, $udyamOccurrences) {

            $mappingData = [];
            $msmeIdsToSync = [];
            $mapped = [];
            $unmapped = [];

            foreach ($records as $record) {

                if (!isset($msmeMap[$record['udyam_no']])) {
                    $unmapped[] = [
                        'udyam_no' => $record['udyam_no'],
                        'bpp_id'   => $record['bpp_id'],
                        'reason'   => 'MSME record not found for this Udyam No',
                    ];
                    continue;
                }

                $msme = $msmeMap[$record['udyam_no']];
                $msmeId = $msme->id;
                $oldBppId = $msme->bpp_id;
                $msmeIdsToSync[] = $msmeId;

                $mapped[] = [
                    'udyam_no'        => $record['udyam_no'],
                    'bpp_id'          => $record['bpp_id'],
                    'old_bpp_id'      => $oldBppId,
                    'is_duplicate'    => ($udyamOccurrences[$record['udyam_no']] ?? 1) > 1,
                    'already_updated' => $oldBppId !== null && $oldBppId === $record['bpp_id'],
                    'updated_at'      => $now->format('d-m-Y H:i:s'),
                ];

                $mappingData[] = [
                    'id'         => (string) Str::uuid(),
                    'snp_id'     => $snpId,
                    'msme_id'    => $msmeId,
                    'status'     => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (empty($msmeIdsToSync)) {
                return [
                    'success' => false,
                    'message' => 'No matching MSME records found',
                    'mapped' => [],
                    'unmapped' => $unmapped,
                ];
            }

        /*
            |--------------------------------------------------------------------------
            | STEP 1: DELETE EXISTING MAPPINGS (PREVENT DUPLICATES)
            |--------------------------------------------------------------------------
            */
            DB::table('team_snpmsme_mapping')
                ->where('snp_id', $snpId)
                ->whereIn('msme_id', $msmeIdsToSync)
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | STEP 2: UPSERT MAPPINGS (FIXED UNIQUE KEY)
            |--------------------------------------------------------------------------
            */
            DB::table('team_snpmsme_mapping')->upsert(
                $mappingData,
                ['snp_id', 'msme_id'],
                [
                    'status',
                    'updated_at'
                ]
            );
            /*
            |--------------------------------------------------------------------------
            | STEP 3: BULK UPDATE MSME SCHEMES (BPP ID)
            |--------------------------------------------------------------------------
            */
            $case = '';
            $bindings = [];
            $msmeIds = [];

            foreach ($records as $record) {

                if (!isset($msmeMap[$record['udyam_no']])) {
                    continue;
                }

                $msmeId = $msmeMap[$record['udyam_no']]->id;
                $msmeIds[] = $msmeId;

                $case .= " WHEN ? THEN ? ";
                $bindings[] = $msmeId;
                $bindings[] = $record['bpp_id'];
            }

            if (empty($msmeIds)) {
                return [
                    'success' => false,
                    'message' => 'No matching MSME records found',
                    'mapped' => [],
                    'unmapped' => $unmapped,
                ];
            }

            $placeholders = implode(',', array_fill(0, count($msmeIds), '?'));

            $bindings = array_merge(
                $bindings,
                [$now],
                $msmeIds
            );

            $sql = "
                UPDATE team_msme_schemes
                SET
                    bpp_id = CASE id
                        {$case}
                    END,
                    bpp_updated_at = ?
                WHERE id IN ({$placeholders})
            ";

            DB::update($sql, $bindings);

            return [
                'success' => true,
                'updated_count' => count($msmeIds),
                'url' => url('/onboarded-msme'),
                'mapped' => $mapped,
                'unmapped' => $unmapped,
            ];
        });
    }

    public function getSnpId(string $snpTeamId): string
    {
        return DB::table('team_snp_scheme')
            ->where('snp_id', $snpTeamId)
            ->value('id') ?? '';
    }
}
