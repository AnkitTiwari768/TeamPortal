<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use Illuminate\Support\Facades\DB;
use App\Domain\FundDistribution\FundDistribution;

class FundDistributionService
{
    /**
     * Get details for editing a fund distribution record.
     */
    public function getFundDistributionEditDetails(string $id): array
    {
        $fundDistribution = FundDistribution::findOrFail($id);

        // Pre-resolve existing cascading lists
        $subComponents = $this->getAttributeValues('major-components', $fundDistribution->major_component_id);
        $subDurations = [];
        if ($fundDistribution->duration_id) {
            $subDurations = $this->getAttributeValues('duration', $fundDistribution->duration_id);
        }

        return [
            'fundDistribution' => $fundDistribution->toArray(),
            'subComponents' => $subComponents,
            'subDurations' => $subDurations,
            'documentUrl' => $this->getDocumentUrl($fundDistribution->upload_document),
            'id' => $id,
        ];
    }

    /**
     * Get details for viewing an individual transaction.
     */
    public function getFundDistributionViewDetails(string $id): array
    {
        $fundDistribution = FundDistribution::findOrFail($id);

        $attributeIds = collect([
            $fundDistribution->duration_id,
            $fundDistribution->sub_duration_id,
            $fundDistribution->major_component_id,
            $fundDistribution->sub_component_id,
        ])->filter()->unique();

        $attributeValues = DB::table('attribute_values')
            ->whereIn('id', $attributeIds)
            ->pluck('attribute_value', 'id');

        return [
            'row' => array_merge(
                $fundDistribution->toArray(),
                [
                    'major_component_name' => $attributeValues[$fundDistribution->major_component_id] ?? 'N/A',
                    'sub_component_name' => $attributeValues[$fundDistribution->sub_component_id] ?? 'N/A',
                    'duration_name' => $attributeValues[$fundDistribution->duration_id] ?? 'Consolidated',
                    'sub_duration_name' => $attributeValues[$fundDistribution->sub_duration_id] ?? 'N/A',
                    'sanction_order_date_formatted' => $fundDistribution->sanction_order_date ? $fundDistribution->sanction_order_date->format('d-m-Y') : null,
                ]
            ),
            'documentUrl' => $this->getDocumentUrl($fundDistribution->upload_document),
        ];
    }

    /**
     * Detail Drilldown Data Generator.
     */
    public function getDrillDownGroupDetails(string $fy, string $majorId, ?string $subId): array
    {
        // 1. Fetch Aggregate Totals (Cards)
        $totalsQuery = DB::table('fund_pools')
            ->where('financial_year', $fy)
            ->where('major_component_id', $majorId);
        
        if ($subId && $subId !== 'null' && $subId !== 'NULL') {
            $totalsQuery->where('sub_component_id', $subId);
        } else {
            $totalsQuery->whereNull('sub_component_id');
        }

        $totals = $totalsQuery->select(
            DB::raw('COALESCE(SUM(total_allocated_amount), 0) as total_allocated'),
            DB::raw('COALESCE(SUM(total_distributed_amount), 0) as total_distributed'),
            DB::raw('COALESCE(SUM(remaining_balance), 0) as remaining')
        )->first();

        // 2. Build Component Names Reference
        $majorName = DB::table('attribute_values')->where('id', $majorId)->value('attribute_value');
        $subName = $subId && $subId !== 'null' && $subId !== 'NULL' ? DB::table('attribute_values')->where('id', $subId)->value('attribute_value') : null;

        // 3. Fetch Durational Breakdowns
        $breakdownQuery = DB::table('fund_pools as fp')
            ->leftJoin('attribute_values as dur', DB::raw('CONVERT(dur.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.duration_id')
            ->leftJoin('attribute_values as sdur', DB::raw('CONVERT(sdur.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.sub_duration_id')
            ->where('fp.financial_year', $fy)
            ->where('fp.major_component_id', $majorId);

        if ($subId && $subId !== 'null' && $subId !== 'NULL') {
            $breakdownQuery->where('fp.sub_component_id', $subId);
        } else {
            $breakdownQuery->whereNull('fp.sub_component_id');
        }

        $breakdowns = $breakdownQuery->select(
            DB::raw('COALESCE(dur.attribute_value, "Consolidated Period") as duration_name'),
            'sdur.attribute_value as sub_duration_name',
            'fp.total_allocated_amount',
            'fp.total_distributed_amount',
            'fp.remaining_balance'
        )->orderBy('duration_name')->get();

        return compact('totals', 'majorName', 'subName', 'breakdowns');
    }

    /**
     * Helper to load attribute values by attribute code and parent id.
     */
    public function getAttributeValues(string $codeOrId, ?string $parentId = null)
    {
        $byCode = DB::table('attributes')->where('code', $codeOrId)->first();

        $query = DB::table('attribute_values as av')
            ->where('av.status', 1)
            ->select(
                'av.id',
                'av.attribute_value as name',
                'av.code',
                'av.parent_id'
            );

        if ($byCode) {
            $query->where('av.attribute_id', $byCode->id);
        } else {
            $query->where('av.attribute_id', $codeOrId);
        }

        if ($parentId !== null) {
            $query->where('av.parent_id', $parentId);
        } else {
            $query->whereNull('av.parent_id');
        }

        return $query->orderBy('av.sort_order')->get();
    }

    /**
     * Get document details for viewing or downloading.
     */
    public function getDocumentDetails(string $id): ?array
    {
        try {
            $fundDistribution = FundDistribution::findOrFail($id);
            if (!$fundDistribution || empty($fundDistribution->upload_document)) {
                return null;
            }

            $file = DB::table('file_uploads')
                ->where('id', $fundDistribution->upload_document)
                ->orWhere('file_system_name', $fundDistribution->upload_document)
                ->first();

            if (!$file) {
                return null;
            }

            return [
                'file_path' => $file->file_path,
                'file_name' => $file->file_name,
                'file_type' => $file->file_type,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Resolve uploaded document URL.
     */
    private function getDocumentUrl(?string $documentPath): ?string
    {
        if (empty($documentPath)) {
            return null;
        }

        $file = DB::table('file_uploads')
            ->where('id', $documentPath)
            ->orWhere('file_system_name', $documentPath)
            ->first();

        return $file
            ? asset($file->file_path)
            : asset('storage/' . $documentPath);
    }
}
