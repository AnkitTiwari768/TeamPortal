<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Web\Allocation\PoolService;
use Carbon\Carbon;

class StoreFundAllocationAction
{
    public function execute(FundAllocationDTO $dto, ?string $id = null): FundAllocation
    {
        return DB::transaction(function () use ($dto, $id) {
            $headerData = [
                'financial_year'        => $dto->financialYear,
                'duration_id'           => $dto->durationId,
                'sub_duration_id'       => $dto->subDurationId,
                'sanction_order_number' => $dto->sanctionOrderNumber,
                'sanction_order_date'   => $this->parseDate($dto->sanctionOrderDate),
                'document_path'         => $dto->documentPath,
                'remarks'               => $dto->remarks,
                'total_amount_allocated'=> $dto->totalAmount,
                'total_available_amount'=> $dto->totalAvailableAmount,
            ];

            $poolService = app(PoolService::class);

            if ($id) {
                // Edit mode
                $fundAllocation = FundAllocation::findOrFail($id);

                // Reverse old impact from fund pools
                foreach ($fundAllocation->componentMappings as $mapping) {
                    $keyData = [
                        'financial_year'     => $fundAllocation->financial_year,
                        'duration_id'        => $fundAllocation->duration_id,
                        'sub_duration_id'    => $fundAllocation->sub_duration_id,
                        'major_component_id' => $mapping->major_component_id,
                        'sub_component_id'   => $mapping->sub_component_id,
                    ];

                    $key = $poolService->resolvePoolKey($keyData);

                    $poolService->updatePoolAllocation($key, - ((float) $mapping->amount));
                }

                // Update header
                $headerData['updated_by'] = AuthId();
                $fundAllocation->update($headerData);

                // Recreate lines/mappings
                $fundAllocation->componentMappings()->delete();
            } else {
                // Create mode
                $headerData['id'] = (string) Str::uuid();
                $headerData['created_by'] = AuthId();
                $fundAllocation = FundAllocation::create($headerData);

                // --- AUTOMATIC CARRY FORWARD LOGIC ---
                if ($dto->applyCarryForward && !empty($dto->subDurationId)) {
                    $currentSubDuration = DB::table('attribute_values')->where('id', $dto->subDurationId)->first();
                    if ($currentSubDuration) {
                        // We also need to check for quarterly/half-yearly duration carrying forward monthly balances, but the logic here 
                        // was previously only checking previous sub durations. Let's adapt it to include monthly pools if it's quarterly/half-yearly.
                        $previousSubDurationIds = DB::table('attribute_values')
                            ->where('parent_id', $dto->durationId)
                            ->where('sort_order', '<', $currentSubDuration->sort_order)
                            ->pluck('id');

                        $poolsToCarryForward = [];

                        if ($previousSubDurationIds->isNotEmpty()) {
                            // Find all pools in previous sub-durations with remaining balance > 0
                            $previousPools = DB::table('fund_pools')
                                ->where('financial_year', $dto->financialYear)
                                ->where('duration_id', $dto->durationId)
                                ->whereIn('sub_duration_id', $previousSubDurationIds)
                                ->where('remaining_balance', '>', 0)
                                ->get();
                            
                            foreach ($previousPools as $pool) {
                                $poolsToCarryForward[] = $pool;
                            }
                        }

                        // Check if we need to carry forward monthly pools for Quarterly/Half-Yearly
                        $duration = DB::table('attribute_values')->where('id', $dto->durationId)->first();
                        $durationName = strtolower($duration->attribute_value ?? '');
                        if (in_array($durationName, ['quarterly', 'half-yearly', 'quarter', 'half yearly'], true)) {
                            $monthlyDuration = DB::table('attribute_values')
                                ->where('attribute_id', $duration->attribute_id)
                                ->where('attribute_value', 'like', '%Monthly%')
                                ->first();

                            if ($monthlyDuration) {
                                $maxMonthlySortOrder = 0;
                                if (in_array($durationName, ['quarterly', 'quarter'], true)) {
                                    $maxMonthlySortOrder = $currentSubDuration->sort_order * 3;
                                } elseif (in_array($durationName, ['half-yearly', 'half yearly'], true)) {
                                    $maxMonthlySortOrder = $currentSubDuration->sort_order * 6;
                                }

                                $validMonthlySubDurationIds = DB::table('attribute_values')
                                    ->where('parent_id', $monthlyDuration->id)
                                    ->where('sort_order', '<=', $maxMonthlySortOrder)
                                    ->pluck('id');

                                if ($validMonthlySubDurationIds->isNotEmpty()) {
                                    $monthlyPools = DB::table('fund_pools')
                                        ->where('financial_year', $dto->financialYear)
                                        ->where('duration_id', $monthlyDuration->id)
                                        ->whereIn('sub_duration_id', $validMonthlySubDurationIds)
                                        ->where('remaining_balance', '>', 0)
                                        ->get();

                                    foreach ($monthlyPools as $pool) {
                                        $poolsToCarryForward[] = $pool;
                                    }
                                }
                            }
                        }

                        // Check if we need to carry forward Quarterly pools for Half-Yearly
                        $isHalfYearly = in_array($durationName, ['half-yearly', 'half yearly', 'half-year', 'half year', 'half_yearly'], true);
                        if ($isHalfYearly) {
                            $quarterlyDuration = DB::table('attribute_values')
                                ->where('attribute_id', $duration->attribute_id)
                                ->where('attribute_value', 'like', '%Quarterly%')
                                ->first();

                            if ($quarterlyDuration) {
                                // First Half -> sort_order = 1 -> Q1, Q2 (sort_orders 1, 2)
                                // Second Half -> sort_order = 2 -> Q3, Q4 (sort_orders 3, 4)
                                $maxQuarterlySortOrder = $currentSubDuration->sort_order * 2;
                                $minQuarterlySortOrder = $maxQuarterlySortOrder - 1;

                                $validQuarterlySubDurationIds = DB::table('attribute_values')
                                    ->where('parent_id', $quarterlyDuration->id)
                                    ->whereBetween('sort_order', [$minQuarterlySortOrder, $maxQuarterlySortOrder])
                                    ->pluck('id');

                                if ($validQuarterlySubDurationIds->isNotEmpty()) {
                                    $quarterlyPools = DB::table('fund_pools')
                                        ->where('financial_year', $dto->financialYear)
                                        ->where('duration_id', $quarterlyDuration->id)
                                        ->whereIn('sub_duration_id', $validQuarterlySubDurationIds)
                                        ->where('remaining_balance', '>', 0)
                                        ->get();

                                    foreach ($quarterlyPools as $pool) {
                                        $poolsToCarryForward[] = $pool;
                                    }
                                }
                            }
                        }

                        $carryForwardMap = [];

                        if (!empty($poolsToCarryForward)) {
                            foreach ($poolsToCarryForward as $prevPool) {
                                $carryAmount = (float) $prevPool->remaining_balance;
                                
                                // Deduct from previous pool
                                $prevKey = [
                                    'financial_year' => $prevPool->financial_year,
                                    'duration_id' => $prevPool->duration_id,
                                    'sub_duration_id' => $prevPool->sub_duration_id,
                                    'major_component_id' => $prevPool->major_component_id,
                                    'sub_component_id' => $prevPool->sub_component_id,
                                ];
                                $poolService->updatePoolAllocation($poolService->resolvePoolKey($prevKey), -$carryAmount);

                                // Map the carry forward amount for the current component
                                $compKey = $prevPool->major_component_id . '_' . ($prevPool->sub_component_id ?? 'NULL');
                                if (!isset($carryForwardMap[$compKey])) {
                                    $carryForwardMap[$compKey] = 0.0;
                                }
                                $carryForwardMap[$compKey] += $carryAmount;
                            }
                        }
                    }
                }
            }

            // Create new mappings and apply new impact
            // We need to ensure that any carry forwarded component without a direct line also gets a line!
            $finalLines = [];
            foreach ($dto->allocationLines as $line) {
                $compKey = $line['major_component_id'] . '_' . ($line['sub_component_id'] ?? 'NULL');
                $amount = (float) ($line['amount'] ?? 0);
                
                if (isset($carryForwardMap[$compKey])) {
                    $amount += $carryForwardMap[$compKey];
                    unset($carryForwardMap[$compKey]);
                }
                
                if ($amount > 0) {
                    $finalLines[] = [
                        'major_component_id' => $line['major_component_id'],
                        'sub_component_id'   => $line['sub_component_id'] ?? null,
                        'amount'             => $amount,
                    ];
                }
            }
            
            // Add remaining carry forward items as their own lines (if user didn't specify them)
            if (isset($carryForwardMap) && !empty($carryForwardMap)) {
                foreach ($carryForwardMap as $compKey => $carryAmt) {
                    if ($carryAmt > 0) {
                        $parts = explode('_', $compKey);
                        $finalLines[] = [
                            'major_component_id' => $parts[0],
                            'sub_component_id'   => $parts[1] !== 'NULL' ? $parts[1] : null,
                            'amount'             => $carryAmt,
                        ];
                    }
                }
            }

            foreach ($finalLines as $line) {
                $amount = (float) $line['amount'];

                $mappingModel = $fundAllocation->componentMappings()->create([
                    'id'                 => (string) Str::uuid(),
                    'major_component_id' => $line['major_component_id'],
                    'sub_component_id'   => $line['sub_component_id'] ?? null,
                    'amount'             => $amount,
                ]);

                // Apply new impact to fund pools (this now includes both carry forward + fresh allocation)
                $keyData = [
                    'financial_year'     => $dto->financialYear,
                    'duration_id'        => $dto->durationId,
                    'sub_duration_id'    => $dto->subDurationId,
                    'major_component_id' => $line['major_component_id'],
                    'sub_component_id'   => $line['sub_component_id'] ?? null,
                ];
                $key = $poolService->resolvePoolKey($keyData);
                $poolService->updatePoolAllocation($key, $amount);
            }

            return $fundAllocation->load('componentMappings');
        });
    }

    private function parseDate(string $date): string
    {
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }
        return Carbon::parse($date)->format('Y-m-d');
    }
}
