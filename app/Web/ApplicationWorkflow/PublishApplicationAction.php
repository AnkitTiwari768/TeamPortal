<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use App\Traits\HasCreateSubject;
use App\Utils\EstimatedServiceDateCalculator;
use App\Utils\UuidGenerator;
use App\Web\AapleIntegration\AapleApplicationStatus;
use App\Web\AapleIntegration\AapleIntegrationConstant;
use App\Web\AapleIntegration\AapleIntegrationService;
use App\Web\ServiceApplication\ApplicationService;
use App\Web\ServiceApplication\ReviewStatus;
use App\Web\Timeline\TimelineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Web\Sms\SmsTrigger; 

class PublishApplicationAction
{
    use HasCreateSubject, SmsTrigger;

    public function __construct(
        private ApplicationWorkflowService $workflowService,
        private AapleIntegrationService $integrationService
    ) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {
            $userId = auth()->user()->id;
            $workflow = $this->workflowService->getUserWorkflow($userId);
            $now = Carbon::now();
            DB::table('rts_services')
                ->where('id', $data['application_id'])
                ->update([
                    'status' => ReviewStatus::Approve->value,
                    'review_status' => ReviewStatus::Approve->value,
                    'review_status_updated_at' => Carbon::now(),
                    'review_status_updated_by' => $userId,
                    'is_published' => true,
                    'published_at' => $now,
                    'published_by' => $userId
                ]);

            DB::table('service_workflow_logs')
                ->insert([
                    'id' => UuidGenerator::uuid7(),
                    'rts_service_id' => $data['application_id'],
                    'workflow_level' => $workflow->level,
                    'workflow_id' => $workflow->id,
                    'status' => ReviewStatus::Approve->value,
                    'comments' => $data['comments'] ?? null,
                    'created_at' => $now,
                    'created_by' => $userId
                ]);

            TimelineService::addApprovalDocument(
                serviceId: $data['application_id'],
                subject: $this->createSubject(ReviewStatus::Approve->value),
                comment: $dto?->comments ?? null,
                status: ReviewStatus::getName(ReviewStatus::Approve->value)
            );

            $application = DB::table('rts_services')->where('id', $data['application_id'])->first();
            $user = DB::table('users')->where('id', $application->created_by)->first();
            $serviceConfiguration = DB::table('service_configuration')->where('service_category_id',  $application->service_category_id)->first();

            $this->sendApplicationApprovedAlert($application->application_number,$application->mobile);

            if (isset($application->aple_sarkar_track_id) && !empty($application->aple_sarkar_track_id)) {

                $trackId = $application->aple_sarkar_track_id;
                $clientCode = AapleIntegrationConstant::CLIENT_CODE;
                $userId = $user->aple_sarkar_user_id ?? null;
                $serviceId = $serviceConfiguration->code;
                $applicationNumber = $application->application_number;
                $paymentStatus = $application->aple_sarkar_payment_status ? 'Y' : 'N';
                $paymentDate = $application->aple_sarkar_payment_status_updated_at;
                $digitalSign = 'N';
                $digitalSignDate = 'NA';
                $estimatedServiceDays = $serviceConfiguration->estimated_service_days;
                $estimatedServiceDate = EstimatedServiceDateCalculator::addWorkingDays(date('Y-m-d'), $serviceConfiguration->estimated_service_days);
                $amount = '50.50';
                $requestFlag = AapleIntegrationConstant::FLAG_PAYMENT;
                $applicationStatus = AapleApplicationStatus::STATUS_APPLICATION_APPROVED->value;
                $remark = 'Application Approved';
                $UD1 = 'NA';
                $UD2 = 'NA';
                $UD3 = 'NA';
                $UD4 = 'NA';
                $UD5 = 'NA';
                $checkSumKey = AapleIntegrationConstant::CHECKSUM_KEY;

                $stringBeforeChecksum = "$trackId|$clientCode|$userId|$serviceId|$applicationNumber|$paymentStatus|$paymentDate|$digitalSign|$digitalSignDate|$estimatedServiceDays|$estimatedServiceDate|$amount|$requestFlag|$applicationStatus|$remark|$UD1|$UD2|$UD3|$UD4|$UD5|$checkSumKey";

                $checkSumValue = $this->integrationService->generateCheckSumValue($stringBeforeChecksum);

                $finalString = "$trackId|$clientCode|$userId|$serviceId|$applicationNumber|$paymentStatus|$paymentDate|$digitalSign|$digitalSignDate|$estimatedServiceDays|$estimatedServiceDate|$amount|$requestFlag|$applicationStatus|$remark|$UD1|$UD2|$UD3|$UD4|$UD5|$checkSumValue";

                $encryptedStr = $this->integrationService->simpleTripleDes($finalString, AapleIntegrationConstant::ENCRYPT_KEY, AapleIntegrationConstant::ENCRYPT_IV);

                $curl = curl_init();

                $soapRequest = '<?xml version="1.0" encoding="utf-8"?>
                    <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                                    xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
                    <soap12:Body>
                        <SetAppStatus xmlns="http://tempuri.org/">
                        <EncyKey>' . $encryptedStr . '</EncyKey>
                        <DeptCode>MMRDADept</DeptCode>
                        </SetAppStatus>
                    </soap12:Body>
                    </soap12:Envelope>';

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://testcitizenservices.MahaITgov.in/Dept_Authentication.asmx?WSDL',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $soapRequest,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/soap+xml'
                    ),
                ));

                $soapResponse = curl_exec($curl);

                curl_close($curl);

                if (!$soapResponse) return response('SOAP call failed.', 500);

                $xml = simplexml_load_string(trim($soapResponse));

                $namespaces = $xml->getNamespaces(true);
                $body = $xml->children($namespaces['soap'])->Body;
                $response = $body->children('http://tempuri.org/')->SetAppStatusResponse;
                $encryptedHex = (string) $response->SetAppStatusResult;

                $decrypted = $this->integrationService->simpleTripleDesDecrypt($encryptedHex, AapleIntegrationConstant::ENCRYPT_KEY, AapleIntegrationConstant::ENCRYPT_IV);

                $data = json_decode(json_encode(simplexml_load_string($decrypted)), true);

                $logData = [
                    'id' => UuidGenerator::uuid7(),
                    'full_url' => request()->fullUrl(),
                    'request_type' => 'OUT',
                    'soap_request' => $soapRequest,
                    'soap_response' => $soapResponse,
                    'formatted_response' => json_encode($data),
                    'final_string' => $finalString,
                    'created_at' => Carbon::now()
                ];

                DB::table('rts_web_service_integration_logs')->insert($logData);
            }
        });
    }

    public function sendApprovalRequest(array $data) {
       //9f774aab-4f2b-4e84-9100-5e694284fd5e permission id of => Can approve NOC
        DB::transaction(function () use ($data) {
        $userId = auth()->user()->id;
        $workflow = $this->workflowService->getWorkflowsPermissionByID($data['service_id'],'9f657d36-51e4-4bf9-a8b2-6024990b10f5');
            foreach($workflow as $row){
                DB::table('service_workflow_logs')
                    ->insert([
                    'id' => UuidGenerator::uuid7(),
                    'rts_service_id' => $data['application_id'],
                    'workflow_level' => $row->level,
                    'workflow_id' => $row->id,
                    'status' => ReviewStatus::Pending->value,
                    'comments' => $data['comments'] ?? null,
                    'created_at' => Carbon::now(),
                    'created_by' => $userId
                ]);
            }


            TimelineService::addApprovalDocument(
                serviceId: $data['application_id'],
                subject: 'NOC has been sent for approval by '.auth()->user()->full_name,
                comment: $dto?->comments ?? null,
                status: ReviewStatus::getName(ReviewStatus::Pending->value)
            );
        });
    }
}
