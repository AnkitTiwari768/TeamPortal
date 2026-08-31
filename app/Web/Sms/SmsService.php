<?php

declare(strict_types=1);

namespace App\Web\Sms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SmsService
{
    /**
     * Get SMS Template Details
     */
    public function getSmsTemplateDetails(TemplateName $templateName)
    {
        return DB::table('sms_templates')
            ->where('template_name', $templateName->value)
            ->first();
    }

    /**
     * Send SMS
     */
    public function sendSms(
        string $mobile,
        string $message,
        string $entityId,
        string $templateId
    ): bool {

        $url = 'https://api.smartping.ai/fe/api/v1/send?';

        $params = [
            'username'               => env('SMARTPING_USERNAME', 'nsicotp1.trans'),
            'password'               => env('SMARTPING_PASSWORD', 'i0KkM'),
            'unicode'                => false,
            'from'                   => env('SMARTPING_SENDER_ID', 'NSICTI'),
            'to'                     => $mobile,
            'dltContentId'           => $templateId,
            'dltPrincipalEntityId'   => $entityId,
            'text'                   => $message,
        ];

        $datastring = http_build_query($params);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datastring);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);
        $error  = curl_error($ch);

        curl_close($ch);

        /*
        |--------------------------------------------------------------------------
        | Log SMS Request & Response
        |--------------------------------------------------------------------------
        */
        DB::table('sms_logs')->insert([
            'sms_request' => json_encode([
                'url'    => $url,
                'params' => $params,
            ]),

            'sms_response' => json_encode([
                'response' => $result,
                'error'    => $error,
            ]),

            'logged_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | CURL Failure
        |--------------------------------------------------------------------------
        */
        if (!empty($error)) {

            $this->sendFailureMail(
                $mobile,
                $message,
                $entityId,
                $templateId,
                $error
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty Response
        |--------------------------------------------------------------------------
        */
        if (empty($result)) {

            $this->sendFailureMail(
                $mobile,
                $message,
                $entityId,
                $templateId,
                'Empty response received from SmartPing API.'
            );

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | SmartPing Failure Check
        |--------------------------------------------------------------------------
        |
        | Modify this according to actual SmartPing response.
        |
        */

        if (
            stripos($result, 'success') === false &&
            stripos($result, 'accepted') === false
        ) {

            $this->sendFailureMail(
                $mobile,
                $message,
                $entityId,
                $templateId,
                $result
            );

            return false;
        }

        return true;
    }

    /**
     * Send OTP
     */
    public function sendotp(
        $mobile = '8375002539',
        $otp_code = '123456'
    ): bool {

        $msgText = "Your MSME TEAM Initiative registration Verification code is "
            . $otp_code
            . ". Please do not share it with anybody. - NSIC";

        return $this->sendSms(
            $mobile,
            $msgText,
            '1001587390000017440',
            '1007883600541909326'
        );
    }

    /**
     * Failure Email Alert
     */
    private function sendFailureMail(
        string $mobile,
        string $message,
        string $entityId,
        string $templateId,
        string $reason
    ): void {

        try {

            Mail::raw(
                "SMS Sending Failed.\n\n"

                . "Mobile Number : {$mobile}\n"
                . "Entity ID     : {$entityId}\n"
                . "Template ID   : {$templateId}\n\n"

                . "SMS Content:\n"
                . "{$message}\n\n"

                . "Failure Reason:\n"
                . "{$reason}\n\n"

                . "Time: " . now(),

                function ($mail) {

                $mail->to('ankit.tiwari@uneecops.in')
                    ->subject('SMS Delivery Failure Alert');
                }
            );

        } catch (\Throwable $e) {

            Log::error('SMS Failure Alert Email Error', [

                'message' => $e->getMessage(),

                'file' => $e->getFile(),

                'line' => $e->getLine(),
            ]);
        }
    }
}