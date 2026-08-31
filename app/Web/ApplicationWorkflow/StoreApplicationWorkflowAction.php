<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use App\Traits\HasCreateSubject;
use App\Utils\UuidGenerator;
use App\Web\Claim\ClaimReviewStatus;
use App\Web\Sms\SmsHelper;
use App\Web\Sms\SmsService;
use App\Web\Sms\TemplateName;
use App\Web\Timeline\TimelineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\PHPMailerService;

class StoreApplicationWorkflowAction
{
    use HasCreateSubject;

    public function __construct(
        private ApplicationWorkflowService $applicationWorkflowService
    ) {}

    public function execute(ApplicationWorkflowDto $dto): void
    {
        DB::transaction(function () use ($dto) {

            $user = auth()->user();
            //new code for revert
            $detail = DB::table('claims as c')
                    ->select('c.is_reverted','c.reverted_by')
                    ->where('c.id', $dto->application_id)
                    ->first();
            
            if($detail->is_reverted == 1 && $dto->action != ClaimReviewStatus::REVERTED->value)
            { 
                $roleSlug = DB::table('roles as r')
                    ->select('r.slug')
                    ->join('user_roles as ur', 'ur.role_id', '=', 'r.id')
                    ->where('ur.user_id', $detail->reverted_by)
                    ->first();
                
                if(hasRole('ondc-admin'))
                {
                    if($roleSlug->slug == 'nsic')
                    {
                        $payload= [
                            'ondc_review_status' => ClaimReviewStatus::APPROVED->value,
                            'nsic_review_status' => ClaimReviewStatus::PENDING->value,
                            'ondc_review_status_updated_at' => Carbon::now(),
                            'ondc_review_status_updated_by' => $user->id,
                            'is_reverted' => 0
                        ];
                        $this->updateClaimStatus($dto->application_id,$payload);
                    }
                    if($roleSlug->slug == 'nsic-finance')
                    {
                        $payload= [
                            'ondc_review_status' => ClaimReviewStatus::APPROVED->value,
                            'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value,
                            'ondc_review_status_updated_at' => Carbon::now(),
                            'ondc_review_status_updated_by' => $user->id,
                            'is_reverted' => 0
                        ];
                        $this->updateClaimStatus($dto->application_id,$payload);
                    }
                }
                if(hasRole('nsic'))
                {
                    if($roleSlug->slug == 'nsic-finance')
                    {
                        $payload= [
                            'nsic_review_status' => ClaimReviewStatus::APPROVED->value,
                            'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value,
                            'nsic_review_status_updated_at' => Carbon::now(),
                            'nsic_review_status_updated_by' => $user->id,
                            'is_reverted' => 0
                        ];
                        $this->updateClaimStatus($dto->application_id,$payload);
                    }
                }
                $statusName = ClaimReviewStatus::getName((int) $dto?->action);

                TimelineService::addApprovalDocument(
                    serviceId: $dto->application_id,
                    subject: $this->createSubject($dto?->action),
                    comment: $dto?->comments ?? null,
                    status: $statusName
                );

                
                
            }else{ 
            //end revert
                $nextWorkflow = $this->applicationWorkflowService->getNextServiceWorkflow($user->id);
               
                $workflowData = [];

                //$reviewStatus = ClaimReviewStatus::PENDING->value;
                $reviewStatus = $dto->action;

                if (
                    (hasRole('ondc-admin') || hasRole('nsic') ) &&
                    ($dto->action === ClaimReviewStatus::FORWARDED->value)
                ) {
                    $reviewStatus = ClaimReviewStatus::PENDING->value;
                }

                if (
                    (hasRole('ondc-admin') || hasRole('nsic')) &&
                    ($dto->action === ClaimReviewStatus::REJECTED->value)
                ) {
                    $reviewStatus = ClaimReviewStatus::REJECTED->value;
                }
                $revertTo = '';
                if (
                    (hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-finance')) &&
                    ($dto->action === ClaimReviewStatus::REVERTED->value)
                ) {
                    //nsic-finance
                    if(hasRole('nsic-finance')){
                        if($dto->revert_to=='nsic'){
                            $payload=['is_reverted'=>1,'reverted_by'=>$user->id,'nsic_review_status'=>ClaimReviewStatus::PENDING->value,'nsicfinance_review_status'=>ClaimReviewStatus::REVERTED->value];
                            $reviewStatus = ClaimReviewStatus::PENDING->value;
                            $revertTo = 'nsic';
                        }
                        
                        if($dto->revert_to=='ondc-admin'){
                            $payload=['is_reverted'=>1,'reverted_by'=>$user->id,'ondc_review_status'=>ClaimReviewStatus::PENDING->value,'nsicfinance_review_status'=>ClaimReviewStatus::REVERTED->value];
                            $reviewStatus = ClaimReviewStatus::PENDING->value;
                            $revertTo = 'ondc-admin';
                        }
                       
                        if($dto->revert_to=='snp'){
                           
                            $payload=['is_reverted'=>1,'reverted_by'=>$user->id,'review_status'=>ClaimReviewStatus::REVERTED->value,'nsicfinance_review_status'=>ClaimReviewStatus::REVERTED->value];
                            //$reviewStatus = ClaimReviewStatus::PENDING->value;
                        }
                    }
                    
                    //nsic
                    if(hasRole('nsic')){
                        if($dto->revert_to=='ondc-admin'){
                            $payload=['is_reverted'=>1,'reverted_by'=>$user->id,'ondc_review_status'=>ClaimReviewStatus::PENDING->value,'nsic_review_status'=>ClaimReviewStatus::REVERTED->value];
                            $reviewStatus = ClaimReviewStatus::PENDING->value;
                            $revertTo = 'ondc-admin';
                        }
                        
                        if($dto->revert_to=='snp'){
                            $payload=['is_reverted'=>1,'reverted_by'=>$user->id,'review_status'=>ClaimReviewStatus::REVERTED->value,'nsic_review_status'=>ClaimReviewStatus::REVERTED->value];
                            //$reviewStatus = ClaimReviewStatus::PENDING->value;
                        }
                    }

                    if(hasRole('ondc-admin')){

                        if($dto->revert_to=='snp'){
                            $payload=['is_reverted'=>1,'reverted_by'=>$user->id,'review_status'=>ClaimReviewStatus::REVERTED->value,'ondc_review_status'=>ClaimReviewStatus::REVERTED->value];
                            //$reviewStatus = ClaimReviewStatus::PENDING->value;
                        }
                    }
                    
                    $this->updateClaimStatus($dto->application_id,$payload);
                    
                }
                

                if (
                    hasRole('nsic-finance') &&
                    ($dto->action === ClaimReviewStatus::APPROVED->value)
                ) {
                    $reviewStatus = ClaimReviewStatus::APPROVED->value;
                }

                if (
                    hasRole('nsic-finance') &&
                    ($dto->action === ClaimReviewStatus::REJECTED->value)
                ) {
                    $reviewStatus = ClaimReviewStatus::REJECTED->value;
                }

                
                // start If the action new concept

                if (hasRole('ondc-admin') && in_array($dto->action, [ClaimReviewStatus::FORWARDED->value, ClaimReviewStatus::REJECTED->value])) {
                    if($dto->action === ClaimReviewStatus::FORWARDED->value) {
                        $ondc_status = ClaimReviewStatus::APPROVED->value;
                        $forward = ClaimReviewStatus::SUBMITTED->value;
                        $pending=ClaimReviewStatus::PENDING->value;
                    }elseif($dto->action === ClaimReviewStatus::REJECTED->value){
                        $forward = null;
                        $ondc_status = $dto->action;
                        $pending = null;
                    }

                $payload= [
                    'is_sent_nsic' => $forward,
                    'ondc_review_status' => $ondc_status,
                    'nsic_review_status' =>  $pending,
                    'ondc_review_status_updated_at' => Carbon::now(),
                    'ondc_review_status_updated_by' => $user->id
                ];
                $this->updateClaimStatus($dto->application_id,$payload);
                }

                if (hasRole('nsic') && in_array($dto->action, [ClaimReviewStatus::FORWARDED->value, ClaimReviewStatus::REJECTED->value])) {
                    
                if($dto->action === ClaimReviewStatus::FORWARDED->value) {
                        $ondc_status = ClaimReviewStatus::APPROVED->value;
                        $forward = ClaimReviewStatus::SUBMITTED->value;
                        $pending=ClaimReviewStatus::PENDING->value;
                    }elseif($dto->action === ClaimReviewStatus::REJECTED->value){
                        $forward = null;
                        $ondc_status = $dto->action;
                        $pending=null;
                    }

                $payload= [
                    'is_sent_nsicfinance' => $forward,
                    'nsic_review_status' => $ondc_status,
                    'nsicfinance_review_status' => $pending,
                    'nsic_review_status_updated_at' => Carbon::now(),
                    'nsic_review_status_updated_by' => $user->id
                ];
                $this->updateClaimStatus($dto->application_id,$payload);
                }

                if (hasRole('nsic-finance')) {
                    
    
                    $payload= [
                    'nsicfinance_review_status' => $dto->action,
                    'nsicfinance_review_status_updated_at' => Carbon::now(),
                    'nsicfinance_review_status_updated_by' => $user->id
                    ];
                    $this->updateClaimStatus($dto->application_id,$payload);

                }
                
                // end If the action new concept


                if ($nextWorkflow && !in_array($reviewStatus, [ClaimReviewStatus::REVERTED->value, ClaimReviewStatus::REJECTED->value])) {
                    foreach ($nextWorkflow as $row) {
                        $workflowData[] = [
                            'id' => UuidGenerator::uuid7(),
                            'application_id' => $dto->application_id,
                            'application_type_id' => $row->workflow_type_id,
                            'workflow_id' => $row->id,
                            'workflow_level' => $row->level,
                            'status' => $dto->action,
                            'comments' => $dto->comments,
                            'created_at' => Carbon::now(),
                            'created_by' => $user->id
                        ];
                    }
                }

                if ($workflowData) {
                    DB::table('workflow_logs')->insert($workflowData);
                }


                DB::table('claims')->where('id', $dto->application_id)->update([
                    'status' => $reviewStatus,
                    'review_status' => $reviewStatus,
                    'review_status_updated_at' => Carbon::now(),
                    'review_status_updated_by' => $user->id
                ]);

                if ($dto->action === ClaimReviewStatus::REVERTED->value) {
                    //DB::table('workflow_logs')->where('application_id', $dto->application_id)->update(['is_obselete' => true]);
                }

                $statusName = ClaimReviewStatus::getName((int) $dto?->action);

                TimelineService::addApprovalDocument(
                    serviceId: $dto->application_id,
                    subject: $this->createSubject($dto?->action,$revertTo),
                    comment: $dto?->comments ?? null,
                    status: $statusName
                );


                if (in_array($dto->action, [
                    ClaimReviewStatus::REJECTED->value,
                    ClaimReviewStatus::REVERTED->value,
                    ClaimReviewStatus::APPROVED->value
                ])) {

                    $application = DB::table('claims as c')
                        ->select('c.application_number','ct.name as claim_type', 'u.first_name', 'u.mobile','u.email','c.team_registration_id as msme_id')
                        ->join('claim_types as ct', 'c.claim_type_id', '=', 'ct.id')
                        ->join('users as u', 'c.created_by', '=', 'u.id')
                        ->where('c.id', $dto->application_id)
                        ->first();
                                        
                    $templateData = [
                        'application_number' => $application->application_number,
                        'status' => $statusName,
                        'moduleName' => $application->claim_type,
                        'user' => $application->first_name,
                    ];
                    //dd($templateData);
                    $template = DB::table('sms_templates')
                        ->where('template_name', TemplateName::WORKFLOW_TEMPLATE->value)
                        ->first();

                    if (! $template)
                        throw new \Exception("Inavlid Template");

                    $templateContent = $template->template_content;
                    $templateVariables = [$application->first_name, $application->claim_type, $statusName];
                    $templateVariablesCount = $template->template_variables;
                    $finalMessage = SmsHelper::populateTemplate($templateContent, $templateVariables, $templateVariablesCount);
                    $mobile = $application->mobile;
                    app(SmsService::class)->sendSms($mobile, $finalMessage, $template->entity_id, $template->template_id);
                    $body = view('emails.claim_approve_reject_mail',$templateData)->render();
                    $to = $application->email;
                    $subject = "Your Claim $application->application_number for $application->claim_type has been $statusName";
                    app(PHPMailerService::class)->sendEmail($to, $subject, $body);
                }

                // start If the action new concept
                    //to update msme table is_uploaded column
                    if ($dto->action === ClaimReviewStatus::APPROVED->value && hasRole('nsic-finance')) {
                        DB::table('team_msme_schemes')->where('team_id',$application->msme_id)->update(['is_uploaded' => 2]);
                    }
                // end If the action new concept
            }//end of revert else    
        });
    }

   public function updateClaimStatus(string $claimId,array $payload)
    {
        return DB::table('claims')->where('id', $claimId)->update($payload);
    }
}
