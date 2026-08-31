<?php

declare(strict_types=1);

namespace App\Domain\Msme;

use App\Traits\Respond;

class MsmeController
{
    use Respond;

    public function getMsmeJourney(string $userId, MsmeJourneyAction $action)
    {
        return $this->success($action->execute($userId));
    }

    public function getMsmeDetails(string $userId, MsmeDetailsAction $action)
    {
        return $this->success($action->execute($userId));
    }

    public function getSnpDetails(string $msmeId, SnpDetailsAction $action)
    {
        return $this->success($action->execute($msmeId));
    }
}
