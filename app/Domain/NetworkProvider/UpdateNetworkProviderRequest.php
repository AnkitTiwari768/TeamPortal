<?php

namespace App\Domain\NetworkProvider;

class UpdateNetworkProviderRequest extends RegisterNetworkProviderRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'uuid', 'exists:network_providers,id'],
            ...parent::rules(),
        ];
    }
}
