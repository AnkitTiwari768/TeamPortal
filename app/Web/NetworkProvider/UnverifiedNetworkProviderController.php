<?php

declare(strict_types=1);

namespace App\Web\NetworkProvider;

use App\Domain\NetworkProvider\GetNetworkProviderDetailsAction;
use App\Domain\NetworkProvider\GetNetworkProviderListAction;
use App\Domain\NetworkProvider\NetworkProviderStatus;
use App\Traits\Respond;

final readonly class UnverifiedNetworkProviderController
{
    use Respond;

    public function index()
    {
        guard('unverified-snp-view');

        $title = __('NP Awaiting ONDC Validation');

        return view('snp.unverified-snp-list', compact('title'));
    }

    public function getList()
    {
        guard('unverified-snp-view');

        $data = app(GetNetworkProviderListAction::class)->execute([
            NetworkProviderStatus::PENDING->value,
            NetworkProviderStatus::REVERT->value
        ]);

        return $this->success(data: $data);
    }

    public function show(string $id)
    {
        $title = __('Network Provider Details');

        $isReviewed = false;

        $networkProvider = app(GetNetworkProviderDetailsAction::class)->execute($id);

        return view('snp.details', compact('title', 'networkProvider', 'isReviewed'));
    }
}
