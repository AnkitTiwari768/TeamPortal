<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use App\Web\Workshop\UpdateExecutedWorkshopDTO;
use App\Web\Workshop\Workshop;
use App\Web\Workshop\WorkshopService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UpdateExecutedWorkshopAction
{
    public function execute(UpdateExecutedWorkshopDTO $dto, string $eventId): Workshop
    {
        $userId = auth()->id() ?? null;
        $currentTime = now();

        return DB::transaction(function () use ($dto, $eventId, $userId, $currentTime) {
            /** @var Workshop $workshop */
            $workshop = Workshop::findOrFail($eventId);

            // 1. Update workshop status and remark (only if provided – from modal)
            $workshopUpdate = [];
            if ($dto->status !== null) {
                $workshopUpdate['status'] = $dto->status;
            }
            if ($dto->remark !== null) {
                $workshopUpdate['remark'] = $dto->remark;
            }
            if (!empty($workshopUpdate)) {
                    $workshopUpdate['updated_at'] = Carbon::now();
                    DB::table('workshops')
                        ->where('id', $eventId)
                        ->update($workshopUpdate);
                }

            // 2. Update expense details only if:
            //    a) status is null (edit page) OR
            //    b) status is 'Completed' (modal)
            if ($dto->status === null || $dto->status === 'Completed') {
                // Safe type casting
                $noOfParticipants = (int) ($dto->noOfParticipants ?? 0);
                $expenseAmount = (float) ($dto->expenseAmount ?? 0);
                $nsicFee = (float) ($dto->nsicFee ?? 0);
                $tdsApplicable = $dto->tdsApplicable ?? 'No';
                $tdsPercentage = isset($dto->tdsPercentage) ? (float) $dto->tdsPercentage : null;
                $sanctionOrderNumber = $dto->sanctionOrderNumber ?? '';
                $sanctionOrderDate = $dto->sanctionOrderDate ?? '';
                $supportingDocument = $dto->supportingDocument ?? null;
                $remarks = $dto->remarks ?? '';

                // Calculate TDS and Net Amount
                $tdsAmount = 0.0;
                if ($tdsApplicable === 'Yes' && $tdsPercentage !== null) {
                    $tdsAmount = round(($expenseAmount * $tdsPercentage) / 100, 2);
                }
                $netAmount = round($expenseAmount + $nsicFee - $tdsAmount, 2);

                // Convert sanction order date
                $sanctionDateFormatted = null;
                if (!empty($sanctionOrderDate)) {
                    try {
                        $sanctionDateFormatted = Carbon::createFromFormat('d-m-Y', $sanctionOrderDate)->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::error('Invalid sanction order date: ' . $sanctionOrderDate);
                    }
                }

                $expenseData = [
                    'number_of_participants' => $noOfParticipants,
                    'expense_amount'         => round($expenseAmount, 2),
                    'nsic_fees'              => round($nsicFee, 2),
                    'is_tds_applicable'      => $tdsApplicable === 'Yes' ? 1 : 0,
                    'tds_percentage'         => $tdsPercentage ? round($tdsPercentage, 2) : null,
                    'tds_amount'             => $tdsAmount,
                    'sanction_order_no'      => $sanctionOrderNumber,
                    'sanction_order_date'    => $sanctionDateFormatted,
                    'supporting_documents'   => $supportingDocument ? json_encode([$supportingDocument]) : null,
                    'remarks'                => $remarks,
                    'updated_at'             => $currentTime,
                    'updated_by'             => $userId,
                ];

                $existingExpense = DB::table('workshop_expenses')
                    ->where('workshop_id', $eventId)
                    ->first();

                if ($existingExpense) {
                    DB::table('workshop_expenses')
                        ->where('workshop_id', $eventId)
                        ->update($expenseData);
                } else {
                    $expenseData['id'] = Str::uuid()->toString();
                    $expenseData['workshop_id'] = $eventId;
                    $expenseData['created_at'] = $currentTime;
                    $expenseData['created_by'] = $userId;
                    DB::table('workshop_expenses')->insert($expenseData);
                }

                // 3. Trigger fund distribution ONLY when status is explicitly set to 'Completed'
                if ($dto->status === 'Completed') {
                    app(WorkshopService::class)->processWorkshopFundDistribution($eventId);
                }
            }

            return $workshop->fresh();
        });
    }
}