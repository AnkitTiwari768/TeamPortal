<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClaimDistribution
{
    use Notify;
    /**
     * Fetch batch financial year and duration and check if allocation of this period is exists in the system
     * then insert a entry in fund_distributions table.
     * If allocation is not exists then also insert a row in fund_distributions table (overdraft allowed).
     *
     * @param string $batchId
     * @return string Generated fund distribution ID
     * @throws \Exception
     */
    public function distribute(string $batchId): string
    {
        $batch = DB::table('dy_batches')->where('id', $batchId)->first();
        if (!$batch) {
            throw new \Exception("Batch not found for ID: {$batchId}");
        }

        // 1. Resolve Duration and Sub-Duration from Batch Month
        $durationId = null;
        $subDurationId = null;

        if (!empty($batch->month)) {
            // Fetch root duration value for 'Monthly'
            $monthlyDuration = DB::table('attribute_values as av')
                ->join('attributes as a', 'a.id', '=', 'av.attribute_id')
                ->where('a.code', config('allocation.duration_code', 'duration'))
                ->where('av.attribute_value', 'Monthly')
                ->select('av.id')
                ->first();

            if ($monthlyDuration) {
                $durationId = $monthlyDuration->id;
                // Match correct month child based on numeric sort index
                $matchingMonth = DB::table('attribute_values')
                    ->where('parent_id', $durationId)
                    ->where('sort_order', (int) $batch->month)
                    ->select('id')
                    ->first();

                if ($matchingMonth) {
                    $subDurationId = $matchingMonth->id;
                }
            }
        }

        // 2. Fetch the claim type mapping components
        $claimSlug = DB::table('claim_types')
            ->where('id', $batch->claim_type_id)
            ->value('slug');

        $majorComponentId = null;
        $subComponentId = null;
        if ($claimSlug) {
            $mapping = config("claim_distribution_mapping.{$claimSlug}");
            if ($mapping) {
                $majorComponentId = $mapping['major_component_id'] ?? null;
                $subComponentId = $mapping['sub_component_id'] ?? null;
                if ($subComponentId === 'TODO-REPLACE-WITH-ACTUAL-UUID') {
                    $subComponentId = null;
                }
            }
        }

        // 3. Check if Allocation exists for the financial year and duration
        $allocationExists = false;
        $matchedDurationId = $durationId;
        $matchedSubDurationId = $subDurationId;

        if ($batch->financial_year && $durationId) {
            $allocationExists = DB::table('fund_allocations')
                ->where('financial_year', $batch->financial_year)
                ->where('duration_id', $durationId)
                ->when($subDurationId, fn($q) => $q->where('sub_duration_id', $subDurationId), fn($q) => $q->whereNull('sub_duration_id'))
                ->exists();
        }

        // If specific allocation not found, check broader durations (Quarterly, Half-Yearly, Yearly)
        $durationAttr = DB::table('attributes')->where('code', config('allocation.duration_code', 'duration'))->first();
        if (!$allocationExists && !empty($batch->month) && $durationAttr) {
            // Resolve Quarterly
            $quarterlyDuration = DB::table('attribute_values')
                ->where('attribute_id', $durationAttr->id)
                ->where(function ($q) {
                    $q->where('attribute_value', 'Quarterly')
                        ->orWhere('code', 'quarterly');
                })
                ->first();

            $subQuarterId = null;
            if ($quarterlyDuration) {
                $month = (int) $batch->month;
                $searchPatterns = [];
                $searchCode = '';
                if (in_array($month, [4, 5, 6])) {
                    $searchPatterns = ['First Quarter', '1st Quarter'];
                    $searchCode = 'Q1';
                } elseif (in_array($month, [7, 8, 9])) {
                    $searchPatterns = ['Second Quarter', '2nd Quarter'];
                    $searchCode = 'Q2';
                } elseif (in_array($month, [10, 11, 12])) {
                    $searchPatterns = ['Third Quarter', '3rd Quarter'];
                    $searchCode = 'Q3';
                } elseif (in_array($month, [1, 2, 3])) {
                    $searchPatterns = ['Fourth Quarter', '4th Quarter'];
                    $searchCode = 'Q4';
                }

                $subQuarter = DB::table('attribute_values')
                    ->where('parent_id', $quarterlyDuration->id)
                    ->where(function ($q) use ($searchPatterns, $searchCode) {
                        $q->where('code', $searchCode);
                        foreach ($searchPatterns as $pattern) {
                            $q->orWhere('attribute_value', 'like', "%{$pattern}%");
                        }
                    })
                    ->first();
                $subQuarterId = $subQuarter ? $subQuarter->id : null;
            }

            if ($quarterlyDuration && $subQuarterId) {
                $allocationExists = DB::table('fund_allocations')
                    ->where('financial_year', $batch->financial_year)
                    ->where('duration_id', $quarterlyDuration->id)
                    ->where('sub_duration_id', $subQuarterId)
                    ->exists();
                if ($allocationExists) {
                    $matchedDurationId = $quarterlyDuration->id;
                    $matchedSubDurationId = $subQuarterId;
                }
            }

            // Resolve Half-Yearly
            if (!$allocationExists) {
                $halfYearlyDuration = DB::table('attribute_values')
                    ->where('attribute_id', $durationAttr->id)
                    ->where(function ($q) {
                        $q->where('attribute_value', 'Half-Yearly')
                            ->orWhere('attribute_value', 'Half Yearly')
                            ->orWhere('code', 'half-yearly');
                    })
                    ->first();

                $subHalfId = null;
                if ($halfYearlyDuration) {
                    $month = (int) $batch->month;
                    $searchPatterns = [];
                    $searchCode = '';
                    if (in_array($month, [4, 5, 6, 7, 8, 9])) {
                        $searchPatterns = ['First Half', '1st Half'];
                        $searchCode = 'H1';
                    } else {
                        $searchPatterns = ['Second Half', '2nd Half'];
                        $searchCode = 'H2';
                    }

                    $subHalf = DB::table('attribute_values')
                        ->where('parent_id', $halfYearlyDuration->id)
                        ->where(function ($q) use ($searchPatterns, $searchCode) {
                            $q->where('code', $searchCode);
                            foreach ($searchPatterns as $pattern) {
                                $q->orWhere('attribute_value', 'like', "%{$pattern}%");
                            }
                        })
                        ->first();
                    $subHalfId = $subHalf ? $subHalf->id : null;
                }

                if ($halfYearlyDuration && $subHalfId) {
                    $allocationExists = DB::table('fund_allocations')
                        ->where('financial_year', $batch->financial_year)
                        ->where('duration_id', $halfYearlyDuration->id)
                        ->where('sub_duration_id', $subHalfId)
                        ->exists();
                    if ($allocationExists) {
                        $matchedDurationId = $halfYearlyDuration->id;
                        $matchedSubDurationId = $subHalfId;
                    }
                }
            }

            // Resolve Yearly
            if (!$allocationExists) {
                $yearlyDuration = DB::table('attribute_values')
                    ->where('attribute_id', $durationAttr->id)
                    ->where(function ($q) {
                        $q->where('attribute_value', 'Yearly')
                            ->orWhere('code', 'yearly');
                    })
                    ->first();

                if ($yearlyDuration) {
                    $allocationExists = DB::table('fund_allocations')
                        ->where('financial_year', $batch->financial_year)
                        ->where('duration_id', $yearlyDuration->id)
                        ->whereNull('sub_duration_id')
                        ->exists();
                    if ($allocationExists) {
                        $matchedDurationId = $yearlyDuration->id;
                        $matchedSubDurationId = null;
                    }
                }
            }
        }

        // 4. Calculate total amount for all claims in the batch
        // $totalAmount = (float) DB::table('dy_batch_claims as bc')
        //     ->join('claims as c', 'c.id', '=', 'bc.claim_id')
        //     ->where('bc.batch_id', $batchId)
        //     ->whereNull('bc.is_deleted')
        //     ->sum('c.total_claimed_amount');

        $batchData = DB::table('dy_batches')->where('id', $batchId)->first();
        $totalAmount = (float) $batchData->batch_total_claimed_amount ?? 0.0;
        $totalTdsAmount = (float) $batchData->tds_amount ?? 0.0;
        $totalTdsCgstAmount = (float) $batchData->cgst_tds_amount ?? 0.0;
        $totalTdsSgstAmount = (float) $batchData->sgst_tds_amount ?? 0.0;
        $totalTdsIgstAmount = (float) $batchData->igst_tds_amount ?? 0.0;




        // 5. Try to find or create a matching pool using PoolService and check balance
        $poolId = null;
        $allocatedAmount = 0.0;

        if ($batch->financial_year && $matchedDurationId && $majorComponentId) {
            $poolKey = [
                'financial_year'     => $batch->financial_year,
                'duration_id'        => $matchedDurationId,
                'sub_duration_id'    => $matchedSubDurationId,
                'major_component_id' => $majorComponentId,
                'sub_component_id'   => $subComponentId,
            ];

            // Get existing pool to verify remaining balance
            $existingPool = app(\App\Web\Allocation\PoolService::class)->findPool($poolKey);
            $allocatedAmount = $existingPool ? (float) $existingPool->remaining_balance : 0.0;
        }

        if (!$allocationExists) {
            session()->flash('warning_message', "Reimbursement warning: The allocation for this claim and period does not exist in the system.");
        } elseif ($totalAmount > $allocatedAmount) {
            session()->flash('warning_message', "Reimbursement warning: The claim amount is higher than the allocated amount for this claim and period.");
        }

        // Determine final distribution amount (always positive, even if allocation doesn't exist)
        $distributionAmount = ($totalAmount - $totalTdsAmount - $totalTdsCgstAmount - $totalTdsSgstAmount - $totalTdsIgstAmount);

        if ($batch->financial_year && $matchedDurationId && $majorComponentId) {
            // Resolve or create pool
            $pool = app(\App\Web\Allocation\PoolService::class)->findOrCreatePool($poolKey);
            $poolId = $pool ? $pool->id : null;

            // Update pool distribution and recalculate remaining balance
            app(\App\Web\Allocation\PoolService::class)->updatePoolDistribution($poolKey, $distributionAmount);
        }

        $distributionId = (string) Str::uuid();

        // 6. Insert distribution entry
        DB::table('fund_distributions')->insert([
            'id'                    => $distributionId,
            'financial_year'        => $batch->financial_year,
            'duration_id'           => $matchedDurationId,
            'sub_duration_id'       => $matchedSubDurationId,
            'major_component_id'    => $majorComponentId,
            'sub_component_id'      => $subComponentId,
            'fund_pool_id'          => $poolId,
            'source_type'           => 'BATCH',
            'source_id'             => $batchId,
            'distribution_amount'   => $distributionAmount,
            'tds_percentage'        => 0.0,
            'tds_amount'            => 0.0,
            'net_payable_amount'    => $distributionAmount,
            'sanction_order_number' => $batch->sanction_order_number ?? $batch->batch_number ?? null,
            'sanction_order_date'   => $batch->sanction_order_date ?? now()->toDateString(),
            'remarks'               => $allocationExists
                ? 'Batch Payment allocation exists'
                : 'Batch Payment allocation does not exist',
            'created_by'            => $batch->created_by ?? (function_exists('authId') ? authId() : null),
            'created_at'            => now(),
            'updated_at'            => now()
        ]);

        $this->sendPaymentCompletedNotification($batch);

        return $distributionId;
    }

    private function sendPaymentCompletedNotification($batch)
    {
        $batchNumber = $batch->batch_number;
        $toUserId = $batch->created_by;
        $this->notify(
            toUserId: $toUserId,
            templateKey: 'nsic-finance-adds-pfms-number',
            type: 1,
            message: [
                'BATCH_NUMBER' => $batchNumber
            ]
        );
    }
}
