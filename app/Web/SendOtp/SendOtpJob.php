<?php

declare(strict_types=1);

namespace App\Web\SendOtp;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        private string $userId,
    ) {}

    public function handle(): void
    {
        /*if ($this->type === 'applicant_signup') {
            app(SendOtpService::class)->processSendOtpMsmeRegistraionRequestByMobile($this->userId);
        }else{
            app(SendOtpService::class)->processSendOtpRequestByMobile($this->userId);
        }*/

        app(SendOtpService::class)->processSendOtpForMsmeMappingRequest($this->userId);
      

    }
}
