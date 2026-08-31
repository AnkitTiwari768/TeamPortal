<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Illuminate\Support\Facades\DB;

class FundCarryForwardService
{
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

    public function getClosingBalances(string $financialYear, string $durationId, ?string $subDurationId)
    {
        $query = DB::table('fund_pools as fp')
            ->leftJoin('attribute_values as mc', 'fp.major_component_id', '=', 'mc.id')
            ->leftJoin('attribute_values as sc', 'fp.sub_component_id', '=', 'sc.id')
            ->where('fp.financial_year', $financialYear)
            ->where('fp.duration_id', $durationId);

        if ($subDurationId) {
            $query->where('fp.sub_duration_id', $subDurationId);
        } else {
            $query->whereNull('fp.sub_duration_id');
        }

        $pools = $query->select(
            'fp.major_component_id',
            'mc.attribute_value as major_component_name',
            'fp.sub_component_id',
            'sc.attribute_value as sub_component_name',
            'fp.remaining_balance as closing_balance'
        )->get();

        // 1. Incoming utilization transfers (transferred TO this category as Target)
        $incomingMappings = DB::table('component_utilization_mappings')
            ->where('financial_year', $financialYear)
            ->select(
                'target_major_component_id as major_component_id',
                'target_sub_component_id as sub_component_id',
                DB::raw('SUM(total_max_utilization_amount) as total_incoming')
            )
            ->groupBy('target_major_component_id', 'target_sub_component_id')
            ->get()
            ->keyBy(function ($item) {
                return $item->major_component_id . '|' . ($item->sub_component_id ?? '');
            });

        // 2. Outgoing utilization transfers (transferred FROM this category as Eligible component TO another category)
        $outgoingMappings = DB::table('component_utilization_mapping_details as cumd')
            ->join('component_utilization_mappings as cum', 'cumd.mapping_id', '=', 'cum.id')
            ->where('cum.financial_year', $financialYear)
            ->select(
                'cumd.eligible_major_component_id as major_component_id',
                'cumd.eligible_sub_component_id as sub_component_id',
                DB::raw('SUM(cumd.max_utilization_amount) as total_outgoing')
            )
            ->groupBy('cumd.eligible_major_component_id', 'cumd.eligible_sub_component_id')
            ->get()
            ->keyBy(function ($item) {
                return $item->major_component_id . '|' . ($item->sub_component_id ?? '');
            });

        // Collect unique category keys across pools, incoming, and outgoing mappings
        $allCategoryKeys = collect($pools->map(fn($p) => $p->major_component_id . '|' . ($p->sub_component_id ?? '')))
            ->merge($incomingMappings->keys())
            ->merge($outgoingMappings->keys())
            ->unique();

        $processedPools = collect();

        foreach ($allCategoryKeys as $key) {
            $parts = explode('|', $key);
            $mcId = $parts[0];
            $scId = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null;

            $pool = $pools->first(function ($p) use ($mcId, $scId) {
                return (string)$p->major_component_id === (string)$mcId && (string)($p->sub_component_id ?? '') === (string)($scId ?? '');
            });

            $mcName = $pool ? $pool->major_component_name : DB::table('attribute_values')->where('id', $mcId)->value('attribute_value');
            $scName = $pool ? $pool->sub_component_name : ($scId ? DB::table('attribute_values')->where('id', $scId)->value('attribute_value') : null);
            $baseBalance = $pool ? (float)$pool->closing_balance : 0.0;

            $incoming = isset($incomingMappings[$key]) ? (float)$incomingMappings[$key]->total_incoming : 0.0;
            $outgoing = isset($outgoingMappings[$key]) ? (float)$outgoingMappings[$key]->total_outgoing : 0.0;

            $finalClosingBalance = $baseBalance + $incoming - $outgoing;

            $processedPools->push((object)[
                'major_component_id'   => $mcId,
                'major_component_name' => $mcName ?? '-',
                'sub_component_id'     => $scId,
                'sub_component_name'   => $scName,
                'closing_balance'      => $finalClosingBalance,
                'base_balance'         => $baseBalance,
                'incoming_utilization' => $incoming,
                'outgoing_utilization' => $outgoing,
            ]);
        }

        return $processedPools;
    }

    public function getCarryForwardEditDetails(string $id): array
    {
        $carryForward = FundCarryForward::with('details')->findOrFail($id);

        $detailsFormatted = $carryForward->details->map(function ($detail) {
            return [
                'id' => $detail->id,
                'major_component_id' => $detail->major_component_id,
                'sub_component_id' => $detail->sub_component_id,
                'opening_balance' => $detail->opening_balance,
                'carried_forward_amount' => $detail->carried_forward_amount,
                'remarks' => $detail->remarks,
            ];
        })->values()->toArray();

        return [
            'carryForward' => $carryForward->toArray(),
            'details' => $detailsFormatted,
        ];
    }

    public function getCarryForwardViewDetails(string $id): array
    {
        $carryForward = FundCarryForward::with('details')->findOrFail($id);

        // Collect attribute IDs for translation
        $attributeIds = collect([
            $carryForward->from_duration_id,
            $carryForward->from_sub_duration_id,
            $carryForward->to_duration_id,
            $carryForward->to_sub_duration_id,
        ])
            ->merge($carryForward->details->pluck('major_component_id'))
            ->merge($carryForward->details->pluck('sub_component_id'))
            ->filter()
            ->unique();

        $attributeValues = DB::table('attribute_values')
            ->whereIn('id', $attributeIds)
            ->pluck('attribute_value', 'id');

        $detailsFormatted = $carryForward->details->map(function ($detail) use ($attributeValues) {
            $isUnallocated = $detail->major_component_id === \App\Domain\FundCarryForward\FundCarryForwardDetail::UNALLOCATED_COMPONENT;

            return [
                'id' => $detail->id,
                'major_component_id' => $detail->major_component_id,
                'major_component_name' => $isUnallocated ? 'Unallocated (unmapped)' : ($attributeValues[$detail->major_component_id] ?? '-'),
                'sub_component_id' => $detail->sub_component_id,
                'sub_component_name' => $isUnallocated ? '-' : ($detail->sub_component_id ? ($attributeValues[$detail->sub_component_id] ?? '-') : '-'),
                'opening_balance' => $detail->opening_balance,
                'carried_forward_amount' => $detail->carried_forward_amount,
                'remarks' => $detail->remarks,
            ];
        });

        return [
            'carryForward' => array_merge(
                $carryForward->toArray(),
                [
                    'from_duration_name' => $attributeValues[$carryForward->from_duration_id] ?? '-',
                    'from_sub_duration_name' => $attributeValues[$carryForward->from_sub_duration_id] ?? '-',
                    'to_duration_name' => $attributeValues[$carryForward->to_duration_id] ?? '-',
                    'to_sub_duration_name' => $attributeValues[$carryForward->to_sub_duration_id] ?? '-',
                ]
            ),
            'details' => $detailsFormatted,
        ];
    }
}
