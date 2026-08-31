<?php

declare(strict_types=1);

namespace App\Web\SendOtpForgotUdyam;

use App\Domain\Sms\SmsService;
use App\Domain\Sms\TemplateName;
use App\Services\OtpGenerator;
use Illuminate\Support\Facades\DB;

class SendOtpForgotUdyamNumberAction
{
    public function __construct(
        private SmsService $smsService
    ) {}

    public function execute(string $udyamNumber): array
    {
        $mobile = DB::table('team_msme_schemes')
            ->where('udyam_no', $udyamNumber)
            ->value('mobile');

        if (!$mobile) {
            throw new \Exception("Invalid credentials found!");
        }

        $templateDetails = $this->smsService->getSmsTemplateDetails(TemplateName::MSME_SEND_OTP);

        if (! $templateDetails) {
            throw new \Exception("Invalid Template");
        }

        $generatedOtp = OtpGenerator::generateOtp();

        $templateContent = $templateDetails->template_content;
        $variablesContentCount = $templateDetails->template_variables;
        $templateVariables = ['Test', $generatedOtp];
        $finalMessage = $this->smsService->populateTemplate($templateContent, $templateVariables, $variablesContentCount);

        $this->smsService->sendSMS(
            mobile: $mobile,
            otpCode: $generatedOtp,
            message: $finalMessage,
            entityId: $templateDetails->entity_id,
            templateId: $templateDetails->template_id
        );

        return [
            'mobile' => $mobile,
            'udyam_number' => $udyamNumber
        ];
    }
}
