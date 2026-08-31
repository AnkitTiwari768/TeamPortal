<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Web\Allocation\PoolService;
use Carbon\Carbon;

class StoreFundCarryForwardAction
{
    public function execute(FundCarryForwardDTO $dto, ?string $id = null): FundCarryForward
    {
        return DB::transaction(function () use ($dto, $id) {
            $poolService = app(PoolService::class);

            $headerData = [
                'financial_year'       => $dto->financialYear,
                'from_duration_id'     => $dto->fromDurationId,
                'from_sub_duration_id' => $dto->fromSubDurationId,
                'to_duration_id'       => $dto->toDurationId,
                'to_sub_duration_id'   => $dto->toSubDurationId,
                'carry_forward_date'   => $dto->carryForwardDate ? Carbon::parse($dto->carryForwardDate)->format('Y-m-d') : now()->format('Y-m-d'),
                'remarks'              => $dto->remarks,
                'status'               => 'COMPLETED',
            ];

            if ($id) {
                // Edit Mode
                $carryForward = FundCarryForward::with('details')->findOrFail($id);

                // Reverse old impact from fund pools
                foreach ($carryForward->details as $detail) {
                    $oldAmount = (float) $detail->carried_forward_amount;

                    // Reverse from pool: Add back to from_duration, subtract from to_duration
                    $fromKey = $poolService->resolvePoolKey([
                        'financial_year'     => $carryForward->financial_year,
                        'duration_id'        => $carryForward->from_duration_id,
                        'sub_duration_id'    => $carryForward->from_sub_duration_id,
                        'major_component_id' => $detail->major_component_id,
                        'sub_component_id'   => $detail->sub_component_id,
                    ]);
                    $poolService->updatePoolAllocation($fromKey, $oldAmount);

                    $toKey = $poolService->resolvePoolKey([
                        'financial_year'     => $carryForward->financial_year,
                        'duration_id'        => $carryForward->to_duration_id,
                        'sub_duration_id'    => $carryForward->to_sub_duration_id,
                        'major_component_id' => $detail->major_component_id,
                        'sub_component_id'   => $detail->sub_component_id,
                    ]);
                    $poolService->updatePoolAllocation($toKey, -$oldAmount);
                }

                $headerData['updated_by'] = AuthId();
                $carryForward->update($headerData);

                // Delete old details
                $carryForward->details()->delete();
            } else {
                // Create Mode
                $headerData['id'] = (string) Str::uuid();
                $headerData['created_by'] = AuthId();
                $carryForward = FundCarryForward::create($headerData);
            }

            $totalAmount = 0.00;

            // Save details and apply new impact
            foreach ($dto->carryForwardLines as $line) {
                $carriedAmount = (float) ($line['carried_forward_amount'] ?? 0);
                if ($carriedAmount <= 0) {
                    continue;
                }

                $totalAmount += $carriedAmount;

                $carryForward->details()->create([
                    'id'                     => (string) Str::uuid(),
                    'major_component_id'     => $line['major_component_id'],
                    'sub_component_id'       => $line['sub_component_id'] ?? null,
                    'opening_balance'        => (float) ($line['opening_balance'] ?? 0),
                    'carried_forward_amount' => $carriedAmount,
                    'remarks'                => $line['remarks'] ?? null,
                ]);

                // Deduct from From pool
                $fromKey = $poolService->resolvePoolKey([
                    'financial_year'     => $dto->financialYear,
                    'duration_id'        => $dto->fromDurationId,
                    'sub_duration_id'    => $dto->fromSubDurationId,
                    'major_component_id' => $line['major_component_id'],
                    'sub_component_id'   => $line['sub_component_id'] ?? null,
                ]);
                $poolService->updatePoolAllocation($fromKey, -$carriedAmount);

                // Add to To pool
                $toKey = $poolService->resolvePoolKey([
                    'financial_year'     => $dto->financialYear,
                    'duration_id'        => $dto->toDurationId,
                    'sub_duration_id'    => $dto->toSubDurationId,
                    'major_component_id' => $line['major_component_id'],
                    'sub_component_id'   => $line['sub_component_id'] ?? null,
                ]);
                $poolService->updatePoolAllocation($toKey, $carriedAmount);
            }

            // Update the total amount in header
            $carryForward->update(['total_amount' => $totalAmount]);

            return $carryForward->load('details');
        });
    }
}
