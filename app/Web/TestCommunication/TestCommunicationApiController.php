<?php

declare(strict_types=1);

namespace App\Web\TestCommunication;

use App\Http\Controllers\ClientController;
use App\Services\PHPMailerService;
use App\Domain\EmailTemplate\EmailTemplateService;
use App\Domain\Sms\AirtelSmsProvider;
use App\Domain\Sms\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TestCommunicationApiController extends ClientController
{
    public function sendEmailTest(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'to_email'     => 'required|email',
                'email_mode'   => 'required|in:simple,custom,template',
                'subject'      => 'required_if:email_mode,simple,custom',
                'body'         => 'required_if:email_mode,simple,custom',
                'template_key' => 'required_if:email_mode,template',
                'variables'    => 'array',
            ],
            [
                'to_email.required'       => 'Recipient email is required.',
                'to_email.email'          => 'Please enter a valid email address.',
                'subject.required_if'     => 'Subject is required.',
                'body.required_if'        => 'Body is required.',
                'template_key.required_if'=> 'Please select an email template.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'data' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        try {

            /*
            |--------------------------------------------------------------------------
            | Simple / Custom Email
            |--------------------------------------------------------------------------
            */
            if (in_array($validated['email_mode'], ['simple', 'custom'])) {

                $mailer = app(PHPMailerService::class);

                $success = $mailer->sendEmail(
                    $validated['to_email'],
                    $validated['subject'],
                    $validated['body']
                );

                if ($success) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Test email sent successfully!',
                        'data' => [],
                    ]);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send email. Please check SMTP configuration or logs.',
                    'data' => [],
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | Template Email
            |--------------------------------------------------------------------------
            */
            $emailService = app(EmailTemplateService::class);

            $emailService->send(
                $validated['template_key'],
                $validated['to_email'],
                $validated['variables'] ?? []
            );

            return response()->json([
                'status' => true,
                'message' => 'Template email sent successfully!',
                'data' => [],
            ]);

        } catch (\Throwable $e) {

            Log::error('Email Test Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [
                    // Development only.
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function sendSmsTest(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'mobile'       => ['required', 'regex:/^[6-9]\d{9}$/'],
                'sms_mode'     => 'required|in:custom,template',
                'message'      => 'required_if:sms_mode,custom',
                'entity_id'    => 'required_if:sms_mode,custom',
                'template_id'  => 'required_if:sms_mode,custom',
                'template_name'=> 'required_if:sms_mode,template',
                'variables'    => 'array',
            ],
            [
                'mobile.required'         => 'Mobile number is required.',
                'mobile.regex'            => 'Please enter a valid 10-digit Indian mobile number.',
                'message.required_if'     => 'SMS message content is required.',
                'entity_id.required_if'   => 'DLT Entity ID is required.',
                'template_id.required_if' => 'DLT Template ID is required.',
                'template_name.required_if' => 'Please select an SMS template.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'data' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        try {

            $smsProvider = app(AirtelSmsProvider::class);

            /*
            |--------------------------------------------------------------------------
            | Custom SMS
            |--------------------------------------------------------------------------
            */
            if ($validated['sms_mode'] === 'custom') {

                $success = $smsProvider->sendSMS(
                    $validated['mobile'],
                    $validated['message'],
                    $validated['entity_id'],
                    $validated['template_id']
                );

                if ($success) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Test SMS sent successfully!',
                        'data' => [],
                    ]);
                }

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send SMS. Please check Airtel logs.',
                    'data' => [],
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | Template SMS
            |--------------------------------------------------------------------------
            */
            $templateDetails = DB::table('sms_templates')
                ->where('template_name', $validated['template_name'])
                ->where('template_status', true)
                ->first();

            if (!$templateDetails) {
                return response()->json([
                    'status' => false,
                    'message' => 'Selected SMS template is not found or inactive.',
                    'data' => [],
                ], 404);
            }

            $variables = $validated['variables'] ?? [];

            $expectedCount = (int) $templateDetails->template_variables;

            $message = SmsService::populateTemplate(
                $templateDetails->template_content,
                $variables,
                $expectedCount
            );

            $success = $smsProvider->sendSMS(
                $validated['mobile'],
                $message,
                $templateDetails->entity_id,
                $templateDetails->template_id
            );

            if ($success) {
                return response()->json([
                    'status' => true,
                    'message' => 'Template SMS sent successfully!',
                    'data' => [],
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Failed to send Template SMS.',
                'data' => [],
            ], 500);

        } catch (\Throwable $e) {

            Log::error('SMS Test Error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [
                    // Development only.
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }
}
