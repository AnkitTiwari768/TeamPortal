<?php

namespace App\Jobs;

use App\Enums\NotificationType;
use App\Http\Api\V1\NationalApplication\NationalApplicationNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmissionNotificationJob implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $applicationId) {}

    public function handle()
    {
        (new NationalApplicationNotificationService())
            ->sendNotification(
                type: NotificationType::Submit, 
                applicationId: $this->applicationId
            );
    }
}