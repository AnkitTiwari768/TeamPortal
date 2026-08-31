<?php
namespace App\Web\Sms;
use App\Web\Sms\{SmsHelper, TemplateName, SmsService};

trait SmsTrigger
{
    public function sendLoginOtp(int $otpCode, string $mobile): void
    {	 return;
        $this->sendOtp($otpCode, $mobile, TemplateName::OTP_FOR_RTS_APPLICATION);
    }



    public function sendOtpDuringSighup(int $otpCode, string $mobile): void
    {return;
        $this->sendOtp($otpCode, $mobile, TemplateName::OTP_FOR_RTS_APPLICATION);
    }   


     

    public function sendLoginDetailAlert(string $mobile, string $password){
    	return;
    	$smsService = app(SmsService::class);
        $templateData = $smsService->getSmsTemplateDetails(TemplateName::USER_REGISTRATION);
         if (! $templateData) throw new \Exception("Invalid Template");
         $template = $templateData->template_content;
        $templateVariables = [$mobile, $password];
        $templateVariablesCount = $templateData->template_variables;
        $finalMessage = SmsHelper::populateTemplate($template, $templateVariables, $templateVariablesCount);
        $smsService->sendSms($mobile, $finalMessage,$templateData->entity_id,$templateData->template_id); 
    }

    public function sendQueryAlertToApplicant($result){
    	return;
    	$smsService = app(SmsService::class);
        $templateData = $smsService->getSmsTemplateDetails(TemplateName::QUERY_RAISED);
         if (! $templateData) throw new \Exception("Inavlid Template");
         $template = $templateData->template_content;
        $templateVariables = [$result->application_number];
        $templateVariablesCount = $templateData->template_variables;
        $finalMessage = SmsHelper::populateTemplate($template, $templateVariables, $templateVariablesCount);
        $smsService->sendSms($result->mobile, $finalMessage,$templateData->entity_id,$templateData->template_id); 
    }

    public function sendQueryReplyToApprovar(string $queryId): void
    {   return;
         $query = \DB::table('rts_service_queries')
		    ->join('users', 'users.id', '=', 'rts_service_queries.raised_by')
		    ->join('rts_services', 'rts_services.id', '=', 'rts_service_queries.rts_service_id')
		    ->where('rts_service_queries.id', $queryId)
		    ->select('users.mobile','application_number')
		    ->first(); 

        $smsService = app(SmsService::class);

        $templateData = $smsService->getSmsTemplateDetails(TemplateName::DEFICIENCY_RESPONSE);
         if (! $templateData) throw new \Exception("Inavlid Template");
         $template = $templateData->template_content;
        $templateVariables = [$query->application_number];
        $templateVariablesCount = $templateData->template_variables;
        $finalMessage = SmsHelper::populateTemplate($template, $templateVariables, $templateVariablesCount); 
        $smsService->sendSms($query->mobile, $finalMessage,$templateData->entity_id,$templateData->template_id); 
    }

    public function sendApplicationSubmissionAlert($application_number,$mobile){
    	return;
    	$smsService = app(SmsService::class);
        $templateData = $smsService->getSmsTemplateDetails(TemplateName::APPLICATION_SUBMIT);
         if (! $templateData) throw new \Exception("Inavlid Template");
         $template = $templateData->template_content;
        $templateVariables = [$application_number];
        $templateVariablesCount = $templateData->template_variables;
        $finalMessage = SmsHelper::populateTemplate($template, $templateVariables, $templateVariablesCount);
        $smsService->sendSms($mobile, $finalMessage,$templateData->entity_id,$templateData->template_id); 
    }

    public function sendApplicationReceivedAlert($application_number,$mobile){
    	return;
    	$smsService = app(SmsService::class);
        $templateData = $smsService->getSmsTemplateDetails(TemplateName::APPLICATION_RECEIVED_BY_ADMIN);
         if (! $templateData) throw new \Exception("Inavlid Template");
         $template = $templateData->template_content;
        $templateVariables = [$application_number];
        $templateVariablesCount = $templateData->template_variables;
        $finalMessage = SmsHelper::populateTemplate($template, $templateVariables, $templateVariablesCount);
        $smsService->sendSms($mobile, $finalMessage,$templateData->entity_id,$templateData->template_id); 
    }

    public function sendApplicationApprovedAlert($application_number,$mobile){
    	return;
    	$smsService = app(SmsService::class);
        $templateData = $smsService->getSmsTemplateDetails(TemplateName::APPLICATION_APPROVAL);
         if (! $templateData) throw new \Exception("Inavlid Template");
         $template = $templateData->template_content;
        $templateVariables = [$application_number];
        $templateVariablesCount = $templateData->template_variables;
        $finalMessage = SmsHelper::populateTemplate($template, $templateVariables, $templateVariablesCount);
        $smsService->sendSms($mobile, $finalMessage,$templateData->entity_id,$templateData->template_id); 
    }

    public function sendForgetOtpAlert($otpCode,$mobile){
        return;
         $this->sendOtp($otpCode, $mobile, TemplateName::OTP_FOR_RTS_APPLICATION);
    }



    public function sendOtpMsmeMapping(int $otpCode, string $mobile): void
    { 
        $this->sendOtp($otpCode, $mobile, TemplateName::MSME_MAPPING);
    }  

    
    public function sendOtpMsmeRegistration(int $otpCode, string $mobile): void
    { 
        $this->sendOtp($otpCode, $mobile, TemplateName::MSME_REGISTRATION);
    }  


    private function sendOtp(int $otpCode, string $mobile, TemplateName $templateConstant): void
    { 
        $smsService = app(SmsService::class);

        $templateData = $smsService->getSmsTemplateDetails($templateConstant);
        //dd($templateData);
        if (! $templateData) {
            throw new \Exception("Invalid Template");
        }

        $template = $templateData->template_content;

        if (!empty(auth()->user()->first_name)) {
            $templateVariables = [auth()->user()->first_name, $otpCode];
        } else {
            $templateVariables = [$otpCode];
        }
        
       
        $templateVariablesCount = $templateData->template_variables;

    
        $finalMessage = SmsHelper::populateTemplate($template, $templateVariables, $templateVariablesCount);
        

        $smsService->sendSms($mobile, $finalMessage, $templateData->entity_id, $templateData->template_id);
    }

    
}
