<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Domain\EmailTemplate\EmailTemplateService;
use App\Enums\EntityType;
use App\Web\Timeline\HasTimeline;
use Illuminate\Support\Facades\DB;

class RevertNetworkProviderAction
{
    use HasTimeline;

    public function execute(array $validatedData)
    {
        DB::transaction(function () use ($validatedData) {

            $networkProvider = NetworkProvider::where('id', $validatedData['network_provider_id'])->first();

            NetworkProvider::where('id', $validatedData['network_provider_id'])->update([
                'status' => NetworkProviderStatus::REVERT->value,
                'status_updated_at' => now(),
                'review_remarks' => $validatedData['remarks'] ?? null,
            ]);

            $authUserName = auth()->user()->first_name;

            $this->addTimeline([
                'entity_id' => $networkProvider->id,
                'entity_type' => EntityType::NETWORK_PROVIDER->value,
                'subject' => "The registration request of NP - {$networkProvider->organization_name} has been reverted by {$authUserName}",
                'comment' => $validatedData['remarks'] ?? null,
                'status' => 'Reverted'
            ]);

            app(EmailTemplateService::class)->send(
                templateKey: 'np-registration-reverted',
                toEmail: $networkProvider->email,
                data: [
                    'name' => $networkProvider->organization_name,
                    'status' => 'Reverted',
                    'revert_link' => url('np-registration/' . $networkProvider->id),
                    'remarks' => $validatedData['remarks'] ?? null,
                    'text' => 'for registering'
                ]
            );
        });
    }
}
