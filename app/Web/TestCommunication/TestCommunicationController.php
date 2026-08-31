<?php

declare(strict_types=1);

namespace App\Web\TestCommunication;

use App\Http\Controllers\ClientController;
use App\Web\Sms\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class TestCommunicationController extends ClientController
{
     
    public function index(): View
    {
        // Authorization check - only ankit.tiwari@uneecops.in can access
        $user = Auth::user();
        // if (!$user || $user->email !== 'ankit.tiwari@uneecops.in') {
        //     abort(403, 'You are not authorized to access this page.');
        // }
        
        $title = 'Communication Testing Dashboard';

        $emailTemplates = DB::table('email_templates')
            ->where('is_active', true)
            ->get()
            ->map(function ($template) {
                if (is_string($template->variables)) {
                    $template->variables = json_decode($template->variables, true);
                }
                return $template;
            });

        $smsTemplates = DB::table('sms_templates')
            ->where('template_status', true)
            ->get();

        return view('test_communication', compact('title', 'emailTemplates', 'smsTemplates'));
    }

    /**
     * Send test SMS
     */
    public function sendSmsTest(Request $request): JsonResponse
    {
        // Authorization check for API endpoint too
        $user = Auth::user();
        if (!$user || $user->email !== 'ankit.tiwari@uneecops.in') {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to access this page.',
            ], 403);
        }

        // UPDATE VALIDATION RULES - Added 'simple' mode
        $validator = Validator::make(
            $request->all(),
            [
                'mobile'       => ['required', 'regex:/^[6-9]\d{9}$/'],
                'sms_mode'     => 'required|in:simple,custom,template',
                'message'      => 'required_if:sms_mode,custom|required_if:sms_mode,simple',
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
            // Get the SMS service instance
            $smsService = app(SmsService::class);

            /*
            |--------------------------------------------------------------------------
            | Simple SMS (No DLT required for testing)
            |--------------------------------------------------------------------------
            */
            if ($validated['sms_mode'] === 'simple') {
                // Use your working method directly with hardcoded values or use a test DLT
                // Option 1: Use hardcoded test credentials (same as your working test)
                $success = $smsService->sendSms(
                    $validated['mobile'],
                    $validated['message'],
                    '1001587390000017440',  // Your working entity ID
                    '1007883600541909326'    // Your working template ID
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
                    'message' => 'Failed to send SMS. Please check logs.',
                    'data' => [],
                ], 500);
            }

            /*
            |--------------------------------------------------------------------------
            | Custom SMS
            |--------------------------------------------------------------------------
            */
            if ($validated['sms_mode'] === 'custom') {
                $success = $smsService->sendSms(
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

            $success = $smsService->sendSms(
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
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }
}