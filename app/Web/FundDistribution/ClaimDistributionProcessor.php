<?php

declare(strict_types=1);

namespace App\Web\FundDistribution;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Web\Allocation\PoolService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * ClaimDistributionProcessor
 *
 * Highly localized operational logic responsible for physical DB manipulation.
 * Enforces isolation, row-locking, and meticulous balance subtraction while
 * adhering strictly to the Waterfall Deduction Strategy.
 */
class ClaimDistributionProcessor
{
    protected PoolService $poolService;

    public function __construct(PoolService $poolService)
    {
        $this->poolService = $poolService;
    }

    /**
     * Executes physical deduction routines across targeted candidate pools.
     * This must ALWAYS reside inside an active database transaction context.
     *
     * @param Collection $candidatePools The pools to drain sequentially.
     * @param array $deductionMetadata Standard container with amounts and identifiers.
     * @return array An array of generated Distribution UUIDs for logging/audit.
     * @throws Exception if operational anomalies prevent safe state maintenance.
     */
    public function executeTransactionalDeductions(Collection $candidatePools, array $deductionMetadata): array
    {
        $totalRequired = (float) ($deductionMetadata['amount'] ?? 0.0);

        if ($totalRequired <= 0) {
            throw new Exception("Constraint Error: Claim distributions cannot possess zero or negative balances.");
        }

        $remainingDeduction = $totalRequired;
        $totalCandidates = $candidatePools->count();
        $processedGuids = [];

        Log::info("Processing safe deduction sequence.", [
            'source_type' => $deductionMetadata['source_type'],
            'source_id'   => $deductionMetadata['source_id'],
            'initial_amt' => $totalRequired
        ]);

        // Enumerate through pools chronologically using zero-index
        foreach ($candidatePools->values() as $index => $poolMeta) {

            // Halting condition: Full amount satisfied.
            if ($remainingDeduction <= 0) {
                break;
            }

            /**
             * CRITICAL: Establish a raw DB row lock immediately to forestall 
             * double-deductions from highly concurrent API floods.
             */
            $activePool = DB::table('fund_pools')
                ->where('id', $poolMeta->id)
                ->lockForUpdate()
                ->first();

            if (!$activePool) {
                Log::warning("Candidate pool unexpectedly vaporized during iterative drain. Skipping loop index {$index}.");
                continue;
            }

            // Capture funds UP TO remaining balance of the pool. If pool is empty/negative, skip.
            $posBuffer = max(0.0, (float) $activePool->remaining_balance);
            if ($posBuffer <= 0.0) {
                continue;
            }

            $deductibleFromThisPool = min($posBuffer, $remainingDeduction);

            // Execute write logic if numeric mass allocated
            if ($deductibleFromThisPool > 0.001) { // Guard against micro-float debris
                $generatedDistributionId = $this->writeDistributionInstance(
                    $activePool,
                    $deductibleFromThisPool,
                    $deductionMetadata
                );

                $processedGuids[] = $generatedDistributionId;

                // Delegate pool math to core service
                $this->poolService->updatePoolDistribution([
                    'financial_year'     => $activePool->financial_year,
                    'duration_id'        => $activePool->duration_id,
                    'sub_duration_id'    => $activePool->sub_duration_id,
                    'major_component_id' => $activePool->major_component_id,
                    'sub_component_id'   => $activePool->sub_component_id,
                ], $deductibleFromThisPool);

                $remainingDeduction -= $deductibleFromThisPool;
            }
        }

        // Check if full requested deduction was satisfied without creating negative balance
        // dd($remainingDeduction);
        // if (abs($remainingDeduction) > 0.001) {
        //     Log::error("Insufficient fund balance for claim reimbursement.", ['outstanding' => $remainingDeduction]);
        //     throw new Exception("Reimbursement cannot be made as the claim amount is higher than the allocated amount for this claim and period.");
        // }

        return $processedGuids;
    }

    /**
     * Core factory for static distribution ledger entry generation.
     */
    protected function writeDistributionInstance(object $pool, float $allottedAmount, array $metadata): string
    {
        $uuid = (string) Str::uuid();

        // Standard Tax Calculus Propagation
        $taxPct = (float) ($metadata['tds_percentage'] ?? 0.0);
        $taxAmt = round(($allottedAmount * $taxPct) / 100, 2);
        $netPayable = $allottedAmount - $taxAmt;

        $insertData = [
            'id'                  => $uuid,
            'financial_year'      => $pool->financial_year,
            'duration_id'         => $pool->duration_id,
            'sub_duration_id'     => $pool->sub_duration_id,
            'major_component_id'  => $pool->major_component_id,
            'sub_component_id'    => $pool->sub_component_id,
            'fund_pool_id'        => $pool->id,

            'source_type'         => $metadata['source_type'] ?? 'CLAIM',
            'source_id'           => $metadata['source_id'] ?? null,

            'distribution_amount' => $allottedAmount,
            'tds_percentage'      => $taxPct,
            'tds_amount'          => $taxAmt,
            'net_payable_amount'  => $netPayable,

            'sanction_order_number' => $metadata['reference_number'] ?? null,
            'sanction_order_date'   => now()->toDateString(),
            'remarks'               => $metadata['remarks'] ?? 'Automated Deductive Transfer',

            'created_by'            => $metadata['user_id'] ?? (function_exists('AuthId') ? AuthId() : null),
            'created_at'            => now(),
            'updated_at'            => now()
        ];

        DB::table('fund_distributions')->insert($insertData);

        return $uuid;
    }
}
