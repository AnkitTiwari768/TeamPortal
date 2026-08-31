<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Sms;
use App\Http\Api\V1\User\UserService;
//use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SmsService 
{
  public function __construct(private UserService $userService){
	  $this->authId = AuthId();
	  $this->currentDateTime = currentDateTime();
	  $this->userDetails = $this->userService->getUser($this->authId);
  }
  
  
	public function changedApplicationStatus($scriptTitle,$applicationNumber,$status){
		$query = \DB::table('sms_template_details')->select('message')->where('template_id', '1007161483658159874')->first();
		$message = str_replace('{#var1#}',$scriptTitle,$query->message);
		$message = str_replace('{#var2#}',$applicationNumber,$message);
		$message = str_replace('{#var3#}',$status,$message);
		return $message;
		//The status of your FFO application {#var1#} with Application No {#var2#} is changed to {#var3#}.
	}//FFOSMS21
	
	public function permissionByASITeam($scriptTitle,$applicationNumber){
		$query = \DB::table('sms_template_details')->select('message')->where('template_id', '1007161483654676196')->first();
		$message = str_replace('{#var1#}',$scriptTitle,$query->message);
		$message = str_replace('{#var2#}',$applicationNumber,$message);
		return $message;
		//Permission for your application {#var1#} with Application No. {#var2#} to shoot in India has been approved/rejected by ASI team.
	}//FFOSMS20
	public function submittedToASI(){}//FFOSMS19	
	public function railwayApplicationReceived(){}//FFOSMS18
	public function producerReplyForRailwayApplication(){}//FFOSMS17
	public function railwayQueryForRailwayApplication(){}//FFOSMS16
	public function railwayApplicationRejected(){}//FFOSMS15
	public function railwayApplicationApproved(){}//FFOSMS14
	public function modificationRailwayApplicationSubmitted(){}//FFOSMS13
	public function railwayApplicationSubmitted(){}//FFOSMS12
	public function applicationForStateReceived(){}//FFOSMS11
	public function newQueryReceivedForApplication(){}//FFOSMS10
	public function queryReceivedForApplication(){}//FFOSMS9
	public function producerReplyOnQuery(){}//FFOSMS8
	public function queryPostedByFFO(){}//FFOSMS7
	public function applicationRejected(){}//FFOSMS6
	public function applicationApproved(){}//FFOSMS5
	public function modificationApplicationSubmitted(){}//FFOSMS4
	public function paymentFailed(){}//FFOSMS3
	public function applicationSubmitted(){}//FFOSMS2
	public function registrationsubmitted(){}//FFOSMS1
	
	
	
	public function sendMailForRevrtToFFO($data,$email,$subject,$cc=''){
		if($cc==''){$cc = 'uneecopsteam@gmail.com';}
		if(config('settings.enable_revert_email_to_ffo')){
			try{
				Mail::send('emails.revert_mail_to_FFO', $data, function($message) use ($email,$subject,$cc) {
					$message->to($email)
						->cc($cc)
						->subject($subject)
						->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));  
				});
				$emailContent = View::make('emails.revert_mail_to_FFO', $data)->render();
				$array = [];
				$array['from_email'] = $this->userDetails->email;
				$array['to_email'] = $email;
				$array['cc_email'] = $cc;
				$array['subject'] = $subject;
				$array['body'] = htmlspecialchars($emailContent);
				$array['created_at'] = $this->currentDateTime;
				$array['created_by'] = $this->authId;
				Email::create($array);
			}catch(\Exception $e){
				dd($e);
			}
		}
		
		return ;
	}
}