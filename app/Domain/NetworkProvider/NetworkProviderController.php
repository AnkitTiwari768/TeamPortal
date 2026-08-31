<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Traits\Respond;
use Illuminate\Http\Request;

final readonly class NetworkProviderController
{
    use Respond;

    public function register(RegisterNetworkProviderRequest $request, RegisterNetworkProviderAction $action)
    {
        $provider = $action->execute(validatedData: $request->validated());

        if ($provider) {
            return $this->created(
                message: 'Network Provider registered successfully.',
                data: $provider,
            );
        }

        return $this->error(
            message: 'Error: Profile updation request is already pending for approval',
        );
    }

    public function update(UpdateNetworkProviderRequest $request, UpdateNetworkProviderAction $action)
    {
        $provider = $action->execute(validatedData: $request->validated());

        if ($provider) {
            return $this->success(
                message: 'Network Provider updated successfully.',
                data: $provider,
            );
        }

        return $this->error(
            message: 'Error: Profile updation request is already pending for approval',
        );
    }

    public function getUnverifiedList(GetNetworkProviderListAction $action)
    {
        $providers = $action->execute();

        return $this->success(
            message: 'Unverified Network Providers retrieved successfully.',
            data: $providers,
        );
    }

    public function getNetworkProviderDetails(string $id, GetNetworkProviderDetailsAction $action)
    {
        $provider = $action->execute(id: $id);

        return $this->success(
            message: 'Network Provider details retrieved successfully.',
            data: $provider,
        );
    }

    public function approveNetworkProvider(Request $request, ApproveNetworkProviderAction $action)
    {
        $validatedData = $request->validate([
            'network_provider_id' => 'required|uuid|exists:network_providers,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $action->execute(validatedData: $validatedData);

        return $this->success(
            message: 'Network Provider approved successfully.'
        );
    }

    public function rejectNetworkProvider(Request $request, RejectNetworkProviderAction $action)
    {
        $validatedData = $request->validate([
            'network_provider_id' => 'required|uuid|exists:network_providers,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $action->execute(validatedData: $validatedData);

        return $this->success(
            message: 'Network Provider rejected successfully.'
        );
    }

    public function revertNetworkProvider(Request $request, RevertNetworkProviderAction $action)
    {
        $validatedData = $request->validate([
            'network_provider_id' => 'required|uuid|exists:network_providers,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $action->execute(validatedData: $validatedData);

        return $this->success(
            message: 'Network Provider reverted successfully.'
        );
    }
}
