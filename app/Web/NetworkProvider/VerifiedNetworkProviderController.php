<?php

declare(strict_types=1);

namespace App\Web\NetworkProvider;

use App\Domain\NetworkProvider\GetNetworkProviderDetailsAction;
use App\Domain\NetworkProvider\GetNetworkProviderListAction;
use App\Domain\NetworkProvider\NetworkProviderStatus;
use App\Traits\Respond;

final readonly class VerifiedNetworkProviderController
{
    use Respond;

    public function index()
    {
        guard('verified-snp-view');

        $title = __('Verified NP List');

        return view('snp.verified-snp-list', compact('title'));
    }

    public function getList()
    {
        guard('verified-snp-view');

        $data = app(GetNetworkProviderListAction::class)->execute([
            NetworkProviderStatus::APPROVE->value,
            NetworkProviderStatus::REJECT->value
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
