<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Email;
use App\Http\Api\V1\User\UserService;
use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;



class EmailService 
{
  public function __construct(private UserService $userService){
	  //$this->authId = AuthId();
	  //$this->currentDateTime = currentDateTime();
	  //$this->userDetails = $this->userService->getUser($this->authId);
  }
  
  public function sendEmail(string $to, string $subject, string $templatePath, ?array $templateData = [], ?array $cc = [])
  {
		try
		{
			Mail::send($templatePath, $templateData, function($message) use ($email, $subject, $cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(...config('mail.from_emails'));  
			});		
		}
		catch(\Exception $e)
		{
			dd($e);
		}
  } 
  
  public function sendMailToLegalOfficer($email) {
            return Mail::send('emails.send_mail_to_legalOfficer', [], function($message) use ($email) {
                $message->to($email)
					->cc(config('settings.send_mail_to_CC'))
                    ->subject('You are added as Legal Officer')
                    ->from('no-reply@mom.gov.in', 'ICH');
            });
			return true;        
	}
	
	public function sendMailToSE($data,$email,$subject,$cc='') {
		if($cc==''){$cc = 'uneecopsteam@gmail.com';}
		
		try{
		Mail::send('emails.lettertose', $data, function($message) use ($email,$subject,$cc) {
			$message->to($email)
				->cc($cc)
				->subject($subject)
				->from(env('MAIL_FROM_ADDRESS') , env('MAIL_FROM_NAME'));
		});
		}catch(\Exception $e){
			dd($e);
		}
		// $emailContent = View::make('emails.lettertose')->render();
		// $array = [];
		// $array['from_email'] = $this->userDetails->email;
		// $array['to_email'] = $email;
		// $array['cc_email'] = $cc;
		// $array['subject'] = $subject;
		// $array['body'] = htmlspecialchars($emailContent);
		// $array['created_at'] = $this->currentDateTime;
		// $array['created_by'] = $this->authId;
		// Email::create($array);
		return ;
	}
	
	public function sendMailToMIB($data,$email,$subject,$cc='') {
		if($cc==''){$cc = 'uneecopsteam@gmail.com';}
		
		try{
		Mail::send('emails.lettertomib', $data, function($message) use ($email,$subject,$cc) {
			$message->to($email)
				->cc($cc)
				->subject($subject)
				->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
		});
		}catch(\Exception $e){
			dd($e);
		}
		// $emailContent = View::make('emails.lettertomib')->render();
		// $array = [];
		// $array['from_email'] = $this->userDetails->email;
		// $array['to_email'] = $email;
		// $array['cc_email'] = $cc;
		// $array['subject'] = $subject;
		// $array['body'] = htmlspecialchars($emailContent);
		// $array['created_at'] = $this->currentDateTime;
		// $array['created_by'] = $this->authId;
		// Email::create($array);
		return ;
	}
	
	public function sendRevertMailToApplicantFromFFO($data,$email,$subject,$cc='') {
		if($cc==''){$cc = 'uneecopsteam@gmail.com';}
		if(config('settings.enable_revert_email_applicant_notification')){
			try{
				Mail::send('emails.revert_mail_to_applicant', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
				// $emailContent = View::make('emails.revert_mail_to_applicant', $data)->render();
				// $array = [];
				// $array['from_email'] = $this->userDetails->email;
				// $array['to_email'] = $email;
				// $array['cc_email'] = $cc;
				// $array['subject'] = $subject;
				// $array['body'] = htmlspecialchars($emailContent);
				// $array['created_at'] = $this->currentDateTime;
				// $array['created_by'] = $this->authId;
				// Email::create($array);
			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}
	public function sendMailToApproveOnBehalfOfMHA($data,$email,$subject,$cc=''){
		if($cc==''){$cc = 'uneecopsteam@gmail.com';}
		if(config('settings.enable_approve_email_from_mha')){
			try{
				Mail::send('emails.approve_mail_to_applicant_by_MHA', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
				// $emailContent = View::make('emails.approve_mail_to_applicant_by_MHA', $data)->render();
				// $array = [];
				// $array['from_email'] = $this->userDetails->email;
				// $array['to_email'] = $email;
				// $array['cc_email'] = $cc;
				// $array['subject'] = $subject;
				// $array['body'] = htmlspecialchars($emailContent);
				// $array['created_at'] = $this->currentDateTime;
				// $array['created_by'] = $this->authId;
				// Email::create($array);
			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}
	public function sendMailToApproveOnBehalfOfLegal($data,$email,$subject,$cc=''){
		if($cc==''){$cc = 'uneecopsteam@gmail.com';}
		if(config('settings.enable_approve_email_from_legal')){
			try{
				Mail::send('emails.approve_mail_to_applicant_by_legal', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
				// $emailContent = View::make('emails.approve_mail_to_applicant_by_legal', $data)->render();
				// $array = [];
				// $array['from_email'] = $this->userDetails->email;
				// $array['to_email'] = $email;
				// $array['cc_email'] = $cc;
				// $array['subject'] = $subject;
				// $array['body'] = htmlspecialchars($emailContent);
				// $array['created_at'] = $this->currentDateTime;
				// $array['created_by'] = $this->authId;
				// Email::create($array);
			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}
	
	public function sendMailForApproveRejectByMIB($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		if(config('settings.enable_approve_reject_email_from_mib')){
			try{
				Mail::send('emails.approve_reject_mail_by_MIB', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}

	public function sendMailForApproveRejectByMIBToFFO($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$email = explode(',', $emails->emails);
		if(config('settings.enable_approve_reject_email_from_mib')){
			try{
				Mail::send('emails.approve_reject_mail_by_MIB_for_FFO', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});

			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}
	
	public function sendMailForRevrtToFFO($data,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$email = explode(',', $emails->emails);
			try{
				Mail::send('emails.revert_mail_to_FFO', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
				
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendForwardMailToMIB($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		if(config('settings.enable_approve_reject_email_from_mib')){
			try{
				Mail::send('emails.forward_mail_to_MIB', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}

	public static function sendForwardMailFromMIBToApplicant($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		if(config('settings.enable_approve_reject_email_from_mib')){
			try{
				Mail::send('emails.forward_mail_to_MIB_applicant', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}


	public static function sendMailToApplicant($email,$templateData,$is_filming){
		if($is_filming==1){
			
			Mail::send('emails.send_application_submission_email', $templateData, function($message) use ($email,$templateData) {
                    $message->to($email)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Application Form Submission Confirmation - '.$templateData['script_title']."(".$templateData['application_number'].")")
                        ->from('no-reply@ffo.gov.in', 'ICH');
                });
				
				
			
		}else{
			Mail::send('emails.send_application_submission_email_animation', $templateData, function($message) use ($email,$templateData) {
                    $message->to($email)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Application Form Submission Confirmation - '.$templateData['script_title']."(".$templateData['application_number'].")")
                        ->from('no-reply@ffo.gov.in', 'ICH');
                });
		}
		
		return ;
	}
	public static function sendMailToFFO($templateData,$is_filming){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
		if($is_filming==1){
			Mail::send('emails.send_application_submission_email_ffo', $templateData, function($message) use ($emailArray,$templateData) {
                    $message->to($emailArray)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Application Form Submission Confirmation - '.$templateData['script_title']."(".$templateData['application_number'].")")
                        ->from('no-reply@ffo.gov.in', 'ICH');
                });
				
			
		}else{
			Mail::send('emails.send_application_submission_email_ffo_animation', $templateData, function($message) use ($emailArray,$templateData) {
                    $message->to($emailArray)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Application Form Submission Confirmation - '.$templateData['script_title']."(".$templateData['application_number'].")")
                        ->from('no-reply@ffo.gov.in', 'ICH');
                });
		}
		
		return ;
	}

	public static function sendRaiseQueryMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			$emailContent = View::make('emails.raise_query', $data)->render();			
			Mail::send('emails.raise_query', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
			
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public static function sendReplyForRaiseQueryMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		// $emails = \DB::table('users')
        //     ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
		// 	->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
		// 	->join('roles', 'roles.id', '=', 'user_roles.role_id')	
        //     ->where('roles.slug', 'ffo')
        //     ->limit(1)
        //     ->first();
		// 	$email = explode(',', $emails->emails);
		try{
			Mail::send('emails.reply_against_raise_query', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
			$emailContent = View::make('emails.reply_against_raise_query', $data)->render();
			// $array = [];
			// $array['from_email'] = $this->userDetails->email;
			// $array['to_email'] = $email;
			// $array['cc_email'] = $cc;
			// $array['subject'] = $subject;
			// $array['body'] = htmlspecialchars($emailContent);
			// $array['created_at'] = $this->currentDateTime;
			// $array['created_by'] = $this->authId;
			// Email::create($array);
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}


	public static function applicantModificationAlert($email,$data){ 
		//dd($email); 
		$subject='Application modification request - '.$data['script_title']." (".$data['application_number'].")";
		try{
		Mail::send('emails.application_modication_permission', $data, function($message) use ($email,$data,$subject) { 
                    $message->to($email)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject($subject)
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });
			//\Log::info('Email sent successfully.');
		}catch(\Exception $e){
				 \Log::error('Failed to send email: ' . $e->getMessage());
			}

	}

	public static function applicantModificationFFOAlert($data){
		$subject='Modification completed for  - '.$data['script_title']." (".$data['application_number'].")";

		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$email = explode(',', $emails->emails);
			 
			Mail::send('emails.application_modication_permission', $data, function($message) use ($email,$subject) {
					$message->to($email)
						->cc('uneecopsteam@gmail.com')
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});

	}

	public static function sendMailToFFOForRailways($templateData){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
			
			Mail::send('emails.send_railwayapplication_submission_email_ffo', $templateData, function($message) use ($emailArray,$templateData) {
                    $message->to($emailArray)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Railway Application Submission Confirmation')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendMailToApplicantForRailways($email,$templateData){
			Mail::send('emails.send_railwayapplication_submission_email', $templateData, function($message) use ($email,$templateData) {
                    $message->to($email)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Railway Application Submission Confirmation')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendMailToZonal($email,$templateData){			
		Mail::send('emails.send_railwayapplication_submission_email_zonal', $templateData, function($message) use ($email,$templateData) {
				$message->to($email)
					->cc('uneecopsteam@gmail.com')
					->subject('Railway Application Submission Confirmation')
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});		
		return ;
	}

	public static function sendMailToCentralBoard($email,$templateData){			
		Mail::send('emails.send_railwayapplication_submission_email_zonal', $templateData, function($message) use ($email,$templateData) {
				$message->to($email)
					->cc('uneecopsteam@gmail.com')
					->subject('Railway Application Submission Confirmation')
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});		
		return ;
	}

	public static function sendForwardMailToFFOForRailways($templateData){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
			
			Mail::send('emails.send_forward_railwayapplication_email_ffo', $templateData, function($message) use ($emailArray,$templateData) {
                    $message->to($emailArray)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Railway Application Forwarded')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendForwardMailToApplicantForRailways($email,$templateData){
			Mail::send('emails.send_forward_railwayapplication_email', $templateData, function($message) use ($email,$templateData) {
                    $message->to($email)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Railway Application Forwarded')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendRejectMailForRailwaysFFO($templateData){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
			
			Mail::send('emails.send_railway_reject_email_ffo', $templateData, function($message) use ($emailArray,$templateData) {
                    $message->to($emailArray)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Railway Application Form Rejected')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendRejectMailForRailways($email,$templateData){			
			Mail::send('emails.send_railway_reject_email', $templateData, function($message) use ($email,$templateData) {
                    $message->to($email)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Railway Application Form Rejected')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendRailwayApprovalMailToFFO($templateData){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
			
			Mail::send('emails.send_approval_railwayapplication_email_ffo', $templateData, function($message) use ($emailArray,$templateData) {
                    $message->to($emailArray)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Railway Application Approval Confirmation')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendRailwayApprovalMailToApplicant($email,$templateData){
			Mail::send('emails.send_approval_railwayapplication_email', $templateData, function($message) use ($email,$templateData) {
                    $message->to($email)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('Railway Application Approval Confirmation')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public  static function sendRailwayRevertMail($data,$email,$subject,$cc='') {
		if($cc==''){$cc = 'uneecopsteam@gmail.com';}
		if(config('settings.enable_revert_email_applicant_notification')){
			try{
				Mail::send('emails.railway_revert_mail', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}

	public static function sendRailwayRaiseQueryMail($data,$email,$subject,$cc='') {
		if($cc==''){$cc = 'uneecopsteam@gmail.com';}
		if(config('settings.enable_revert_email_applicant_notification')){
			try{
				Mail::send('emails.railway_raise_query_mail', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		}
		return ;
	}

	public static function sendRailwayReplyMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.railway_reply_mail', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public static function sendrailwayReplyForRaiseQueryMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.railway_reply_against_raise_query', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}


	

	public static function sendMailToFFOForState($templateData){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
			
			Mail::send('emails.state_application_submission_email_ffo', $templateData, function($message) use ($emailArray,$templateData) {
                    $message->to($emailArray)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('State Application Form Submission Confirmation')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendMailToApplicantForState($email,$templateData){
			Mail::send('emails.state_application_submission_email', $templateData, function($message) use ($email,$templateData) {
                    $message->to($email)
                        ->cc('uneecopsteam@gmail.com')
                        ->subject('State Application Form Submission Confirmation')
                        ->from('no-reply@ffo.gov.in', 'ICH');
                });		
		return ;
	}

	public static function sendMailToNodal($email,$templateData){			
		Mail::send('emails.state_application_submission_email_nodal', $templateData, function($message) use ($email,$templateData) {
				$message->to($email)
					->cc('uneecopsteam@gmail.com')
					->subject('State Application Form Submission Confirmation')
					->from('no-reply@ffo.gov.in', 'ICH');
			});		
		return ;
	}

	public static function sendStateApproveRejectMailToApplicant($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.state_approve_reject_mail', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public static function sendStateApproveRejectMailToFFO($data,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$email = explode(',', $emails->emails);
			try{
				Mail::send('emails.state_approve_reject_mail_ffo', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});

			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendMailInterimToFFO($templateData){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
		 
		/*if($emailType==1){
			$emailTemplate='emails.interim_application_submission_template_ffo';
		}else{
			$emailTemplate='emails.interim_final_application_submission_template_ffo';
		}*/
		Mail::send('emails.interim_application_submission_template_ffo', $templateData, function($message) use ($emailArray,$templateData) {
            $message->to($emailArray)
                ->cc('uneecopsteam@gmail.com')
                ->subject('Interim Application Form Submission Confirmation')
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
        });
				
		
		return true;
	}

	public static function sendMailInterimToLineProducer($templateData,$email){	
		Mail::send('emails.interim_application_submission_template', $templateData, function($message) use ($email,$templateData) {
				$message->to($email)
					->cc('uneecopsteam@gmail.com')
					->subject('Interim Application Form Submission Confirmation')
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});		
		return ;
	}

	public static function sendInterimRaiseQueryMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.interim_raise_query', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public function sendInterimRevertMailToApplicantFromFFO($data,$email,$subject) {
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.interim_revert_mail_to_applicant', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}

		return ;
	}

	public static function sendInterimReplyForRaiseQueryMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.interim_reply_against_raise_query', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public static function sendInterimForwardMailToMD($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.interim_forward_mail_to_md', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendInterimForwardMailToApplicant($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.interim_forward_mail_applicant', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendInterimMailForRevrtToFFO($data,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
		$email = explode(',', $emails->emails);
			try{
				Mail::send('emails.interim_revert_mail_to_FFO', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		
		return ;
	}

	public static function sendInterimApproveRejectMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.interim_approve_reject_mail', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendInterimApproveRejectMailToFFO($data,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$email = explode(',', $emails->emails);
			try{
				Mail::send('emails.interim_approve_reject_mail_for_FFO', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});

			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendMailDisbursalToFFO($templateData,$subject){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
		Mail::send('emails.disbursal_application_submission_template_ffo', $templateData, function($message) use ($emailArray,$templateData,$subject) {
            $message->to($emailArray)
                //->cc('uneecopsteam@gmail.com')
                ->subject($subject)
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
				
		
		return true;
	}

	public static function sendMailDisbursalToLineProducer($templateData,$email,$subject){	
		Mail::send('emails.disbursal_application_submission_template', $templateData, function($message) use ($email,$templateData,$subject) {
				$message->to($email)
					//->cc('uneecopsteam@gmail.com')
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
			});		
		return ;
	}

	public static function sendDisbursalRaiseQueryMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.disbursal_raise_query', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					//->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public function sendDisbursalRevertMailToApplicantFromFFO($data,$email,$subject) {
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.disbursal_revert_mail_to_applicant', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						//->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}

		return ;
	}

	public static function sendDisbursalReplyForRaiseQueryMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.disbursal_reply_against_raise_query', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					//->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public static function sendDisbursalForwardMailToMD($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.disbursal_forward_mail_to_md', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						//->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendDisbursalForwardMailToApplicant($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.disbursal_forward_mail_applicant', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						//->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendDisbursalApproveRejectMail($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.disbursal_approve_reject_mail', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						//->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendDisbursalApproveRejectMailToFFO($data,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$email = explode(',', $emails->emails);
			try{
				Mail::send('emails.disbursal_approve_reject_mail_for_FFO', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						//->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});

			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendDisbursalMailForRevrtToFFO($data,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
		$email = explode(',', $emails->emails);
			try{
				Mail::send('emails.disbursal_revert_mail_to_FFO', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						//->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		
		return ;
	}

	public static function sendHoldResumeMailToApplicant($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.hold-resume-mail', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						//->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public static function sendDocumentaryForwardMailToMIB($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.documentary_forward_mail_mib', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public static function sendDocumentaryForwardMailFromMIBToApplicant($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
		try{
			Mail::send('emails.documentary_forward_mail_to_applicant', $data, function($message) use ($email,$subject,$cc) {
				$message->to($email)
					->cc($cc)
					->subject($subject)
					->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
			});
		}catch(\Exception $e){
			dd($e);
		}
		return ;
	}

	public static function sendMailToFFOForDocumentary($templateData){
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$emailArray = explode(',', $emails->emails);
			
			Mail::send('emails.documentary_submission_email_ffo', $templateData, function($message) use ($emailArray,$templateData) {
                    $message->to($emailArray)
                        //->cc('uneecopsteam@gmail.com')
                        ->subject('Documentary Application Submission Confirmation')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public static function sendMailToApplicantForDocumentary($email,$templateData){
			Mail::send('emails.documentary_submission_email', $templateData, function($message) use ($email,$templateData) {
                    $message->to($email)
                        //->cc('uneecopsteam@gmail.com')
                        ->subject('Documentary Application Submission Confirmation')
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
                });		
		return ;
	}

	public function documentaryMailForApproveRejectByMIB($data,$email,$subject){
		$cc = 'uneecopsteam@gmail.com';
			try{
				Mail::send('emails.documentary_approve_reject_mail', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public function documentaryMailForApproveRejectByMIBToFFO($data,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$email = explode(',', $emails->emails);
			try{
				Mail::send('emails.documentary_approve_reject_mail_ffo', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});

			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}

	public function documentaryMailForRevrtToFFO($data,$subject){
		$cc = 'uneecopsteam@gmail.com';
		$emails = \DB::table('users')
            ->select(\DB::raw('GROUP_CONCAT(users.email) as emails'))
			->join('user_roles', 'user_roles.user_id', '=', 'users.id')	
			->join('roles', 'roles.id', '=', 'user_roles.role_id')	
            ->where('roles.slug', 'ffo')
            ->limit(1)
            ->first();
			$email = explode(',', $emails->emails);
			try{
				Mail::send('emails.documentary_revert_mail_to_FFO', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
				
			}catch(\Exception $e){
				dd($e);
			}
		return ;
	}


}