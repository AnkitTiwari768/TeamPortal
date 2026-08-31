<?php

declare(strict_types=1);

namespace App\Web\SendOtp;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOtpMsmeRegistrationJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        private string $username,
        private string $email,
    ) {}

    public function handle(): void
    {   
        app(SendOtpService::class)->processSendOtpForMsmeRegistrationRequest($this->username, $this->email);
      
    }
}
