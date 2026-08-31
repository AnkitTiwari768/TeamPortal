<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Support\Facades\DB;
use App\Web\Notification\SendNotificationEvent;
use Illuminate\Support\Facades\Auth;


class WorkFlowNotification
{
    public function sendRejectedBatchNotification(string $batchId, array $rejectedClaims): void
    {
       
        if ($this->shouldSkip($rejectedClaims)) {
            return;
        }
      
        $batch = $this->getBatch($batchId);

        if (!$batch) {
            return;
        }

        $batchNumber = $batch->batch_number ?? 'N/A';
        $fromUserId = Auth::id();

        $claims = $this->getClaims($rejectedClaims);

        foreach ($claims as $claim) {
            $userIds = $this->getSnpUsers($claim);

            foreach ($userIds as $toUserId) {
                $this->sendNotification($fromUserId, $toUserId, $claim->application_number, $batchNumber);
            }
        }
    }

    private function shouldSkip(array $rejectedClaims): bool
    {
        return empty($rejectedClaims);
    }

    // private function isRestrictedRole(): bool
    // {
    //     return hasRole('snp') || hasRole('bnp') || hasRole('lsp');
    // }

    private function getBatch(string $batchId)
    {
        return DB::table('dy_batches')->where('id', $batchId)->first();
    }

    private function getClaims(array $rejectedClaims)
    {
        return DB::table('claims')
            ->whereIn('id', $rejectedClaims)
            ->select(
                'application_number',
                'snp_id',
                'created_by',
            )
            ->get();
    }

    private function getSnpUsers($claim): array
    {
   
        if (empty($claim->created_by)) {
            return [];
        }

        return DB::table('users')
            ->where('id', $claim->created_by)
            ->pluck('id')
            ->filter()
            ->unique()
            ->toArray();
    }

    private function sendNotification($fromUserId, $toUserId, $claimId, $batchNumber): void
    {
        event(new SendNotificationEvent(
            templateKey: 'any-stage-rejection',
            fromUserId: $fromUserId,
            toUserId: $toUserId,
            formRole: authRoleId(),
            toRole: null,
            message: [
                'CLAIM_ID' => $claimId,
                'Batch_Number' => $batchNumber,
            ]
        ));
    }
}