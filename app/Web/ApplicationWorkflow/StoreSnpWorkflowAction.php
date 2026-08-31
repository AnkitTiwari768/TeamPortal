<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use App\Web\Sms\SmsService;
use App\Traits\HasCreateSubject;
use App\Utils\UuidGenerator;
use App\Web\Claim\ClaimReviewStatus;
use App\Web\Sms\SmsHelper;
use App\Web\Sms\TemplateName;
use App\Web\Timeline\TimelineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Hash;

class StoreSnpWorkflowAction
{
    use HasCreateSubject;

    public function __construct(
        private ApplicationWorkflowService $applicationWorkflowService
    ) {}

    public function execute(SnpApplicationWorkflowDto $dto): void
    {
        DB::transaction(function () use ($dto) {
            
            $user = auth()->user();

            $nextWorkflow = $this->applicationWorkflowService->getNextServiceWorkflow($user->id);

            $workflowData = [];


            if (
                hasRole('ondc-admin') &&
                ($dto->action === ClaimReviewStatus::APPROVED->value)
            ) {
                $reviewStatus = ClaimReviewStatus::APPROVED->value;
            }

            if (
                hasRole('ondc-admin') &&
                ($dto->action === ClaimReviewStatus::REJECTED->value)
            ) {
                $reviewStatus = ClaimReviewStatus::REJECTED->value;
            }


            if ($nextWorkflow && !in_array($dto->action, [ClaimReviewStatus::REVERTED->value, ClaimReviewStatus::REJECTED->value])) {
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

            $application = DB::table('team_snp_scheme as c')
                    ->select('u.first_name', 'u.mobile','u.email','c.user_id','c.snp_id')
                    ->join('users as u', 'c.user_id', '=', 'u.id')
                    ->where('c.id', $dto->application_id)
                    ->first();

            $email = $application->email;
            $templateData['name'] = $application->first_name;
            $templateData['email'] = $application->email;
            $templateData['snp_id'] = $application->snp_id;
            $to = $email;

            if ($dto->action === ClaimReviewStatus::APPROVED->value) {
                //set password for user
                $password = $this->generateStrongPassword(8);

                DB::table('team_snp_scheme')->where('id', $dto->application_id)->update([
                    'status' => 1,
                    'review_status' => ClaimReviewStatus::APPROVED->value,
                    'review_status_updated_at' => Carbon::now(),
                    'review_status_updated_by' => $user->id
                ]);

                $userData = [
                    'password'=>     Hash::make($password),//$password,
                    'status'  =>      1 ,
                    'is_role_mapped'=> 1
                ];
                
                User::where('id', $application->user_id)->update($userData);

                //For mail
                $templateData['password'] = $password;
                $body = view('emails.snp_approval_mail',$templateData)->render();
                $subject = 'Registration Account Approved';
                app(PHPMailerService::class)->sendEmail($to, $subject, $body);
              
            }


            if ($dto->action === ClaimReviewStatus::REJECTED->value) {
                $updatedData = [
                    'status' => ClaimReviewStatus::REJECTED->value,
                    'review_status' => ClaimReviewStatus::REJECTED->value,
                    'review_status_updated_at' => Carbon::now(),
                    'review_status_updated_by' => $user->id
                ];
                $subject = 'Registration Account Rejected';
                $templateData['status'] = 'Rejected';
            } 
            if ($dto->action === ClaimReviewStatus::REVERTED->value) {
                $updatedData = [
                    'review_status' => $dto->action,
                    'review_status_updated_at' => Carbon::now(),
                    'review_status_updated_by' => $user->id
                ];
                $subject = 'Registration Account Reverted';
                $templateData['status'] = 'Reverted';
            }

            

            //Mail for revert reject
            if ($dto->action === ClaimReviewStatus::REJECTED->value || $dto->action === ClaimReviewStatus::REVERTED->value) {
                DB::table('team_snp_scheme')->where('id', $dto->application_id)->update($updatedData);
                $templateData['revertlink'] = url('snp-registration/' . $application->user_id);
                $body = view('emails.snp_revert_reject_mail',$templateData)->render();                
                app(PHPMailerService::class)->sendEmail($to, $subject, $body);
            }

            $statusName = ClaimReviewStatus::getName((int) $dto?->action);

            TimelineService::addApprovalDocument(
                serviceId: $dto->application_id,
                subject: $this->createSubject($dto?->action),
                comment: $dto?->comments ?? null,
                status: $statusName
            );

            if (in_array($dto->action, [
                ClaimReviewStatus::REJECTED->value,
                ClaimReviewStatus::REVERTED->value,
                ClaimReviewStatus::APPROVED->value
            ])) {


                $template = DB::table('sms_templates')
                    ->where('template_name', TemplateName::WORKFLOW_TEMPLATE->value)
                    ->first();

                if (! $template)
                    throw new \Exception("Inavlid Template");

                $templateContent = $template->template_content;
                $templateVariables = [$application->first_name, 'SNP Registration', $statusName];
                $templateVariablesCount = $template->template_variables;
                $finalMessage = SmsHelper::populateTemplate($templateContent, $templateVariables, $templateVariablesCount);
                $mobile = $application->mobile;
                app(SmsService::class)->sendSms($mobile, $finalMessage, $template->entity_id, $template->template_id);
            }
        });
    }

    function generateStrongPassword($length = 8): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()-_=+<>?';

        $all = $upper . $lower . $numbers . $symbols;

        // Ensure the password contains at least one of each
        $password = $upper[random_int(0, strlen($upper) - 1)] .
                    $lower[random_int(0, strlen($lower) - 1)] .
                    $numbers[random_int(0, strlen($numbers) - 1)] .
                    $symbols[random_int(0, strlen($symbols) - 1)];

        // Fill the remaining characters randomly
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle to avoid predictable pattern
        return str_shuffle($password);
    }
}
