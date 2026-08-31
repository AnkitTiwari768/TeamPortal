<?php

declare(strict_types=1);

namespace App\Web\Claim;

class DeleteClaimAction
{
    public function __construct(
        private ClaimService $claimService
    ) {}

    public function execute(string $id): void
    {
        $this->claimService->deleteClaim($id);
    }
}
