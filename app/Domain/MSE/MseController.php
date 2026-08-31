<?php

declare(strict_types=1);

namespace App\Domain\MSE;

use App\Traits\Respond;

class MseController
{
    use Respond;

    public function getOnboardedMSE(ListOnboardedMseAction $action)
    {
        return $this->success(data: $action->execute());
    }

    public function getMseForCatalogueClaim(ListOnboardedMseAction $action)
    {
        return $this->success(data: $action->execute(isClaimForCatalogueCreation: true));
    }

    public function getMseForAccountClaim(ListOnboardedMseAction $action)
    {
        return $this->success(data: $action->execute(isClaimForAccountManagement: true));
    }

    public function getMseForPackagingClaim(ListOnboardedMseAction $action)
    {
        return $this->success(data: $action->execute(isClaimForPackaging: true));
    }
}
