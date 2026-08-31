<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use App\Web\Workshop\UpdateExecutedWorkshopDTO;
use App\Web\Workshop\Workshop;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateExecutedWorkshopAction
{
    /**
     * Execute the update executed workshop action.
     *
     * @param UpdateExecutedWorkshopDTO $dto
     * @param string $eventId
     * @return Workshop
     */
    public function execute(UpdateExecutedWorkshopDTO $dto, string $eventId): Workshop
    {
        $userId = auth()->id() ?? null;
        $currentTime = now();

        return DB::transaction(function () use ($dto, $eventId, $userId, $currentTime) {
            // 1. Update the workshop record status
            $workshop = Workshop::findOrFail($eventId);
            $workshop->update([
                'status'                 => 'Completed', // Once executed/expenses are submitted, mark as Completed
                'updated_by'             => $userId,
                'updated_at'             => $currentTime,
            ]);

            // 2. Process file upload if any
            $supportingDocumentPath = $dto->supportingDocument;

            // 3. Calculate TDS and Net Amount
            $tdsAmount = ($dto->tdsApplicable === 'Yes')
                ? round(($dto->expenseAmount * ($dto->tdsPercentage ?? 0)) / 100, 2)
                : 0.0;

            $netAmount = round($dto->expenseAmount + $dto->nsicFee - $tdsAmount, 2);

            // 4. Update or Insert expense details
            $existingExpense = DB::table('workshop_expenses')
                ->where('workshop_id', $eventId)
                ->first();

            $sanctionDate = $dto->sanctionOrderDate
                ? Carbon::createFromFormat('d-m-Y', $dto->sanctionOrderDate)->format('Y-m-d')
                : null;

            $expenseData = [
                'number_of_participants' => $dto->noOfParticipants,
                'expense_amount'       => round($dto->expenseAmount, 2),
                'nsic_fees'            => round($dto->nsicFee, 2),
                'is_tds_applicable'    => $dto->tdsApplicable === 'Yes' ? 1 : 0,
                'tds_percentage'       => $dto->tdsPercentage ? round($dto->tdsPercentage, 2) : null,
                'tds_amount'           => $tdsAmount,
                'sanction_order_no'    => $dto->sanctionOrderNumber,
                'sanction_order_date'  => $sanctionDate,
                'remarks'              => $dto->remarks,
                'updated_at'           => $currentTime,
                'updated_by'           => $userId,
            ];

            if ($supportingDocumentPath) {
                $expenseData['supporting_documents'] = json_encode($supportingDocumentPath);
            }

            if ($existingExpense) {
                DB::table('workshop_expenses')
                    ->where('workshop_id', $eventId)
                    ->update($expenseData);
            } else {
                $expenseData['id'] = \Illuminate\Support\Str::uuid()->toString();
                $expenseData['workshop_id'] = $eventId;
                $expenseData['created_at'] = $currentTime;
                $expenseData['created_by'] = $userId;
                
                // If no supporting document was uploaded but we are creating the expense record, set default path to null
                if (!isset($expenseData['supporting_documents'])) {
                    $expenseData['supporting_documents'] = null;
                }

                DB::table('workshop_expenses')->insert($expenseData);
            }

            return $workshop;
        });
    }
}
