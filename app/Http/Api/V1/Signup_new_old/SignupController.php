<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Signup;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
//use App\Notifications\Email\Signup as SignupNotification;


class SignupController extends ApiController
{
    public function __construct(private SignupService $signupService) {
	
	}

    public function store(Request $request, ?string $id = null)
    {
		//$request['captcha'] = 'dtxu9';
        $validator = Validator::make($request->all(), SignupRequest::getRules($id));

        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->signupService->store($validator->validated())
        );
    }
	
	public function notification(Request $request){
		echo 'dsfsdf';die;
		//$applicant = User::where('username','pratibha.kushwaha@uneecops.in')->first();
		//$applicant->notify(new SignupNotification($applicant, $payload['password']));
		$data = array('name'=>"Virat Gandhi");
   
      Mail::send(['text'=>'mail'], $data, function($message) {
         $message->to('kushpratibha10@gmail.com', 'Tutorials Point')->subject
            ('Laravel Basic Testing Mail');
         $message->from('unee.php@gmail.com','Virat Gandhi');
      });
      echo "Basic Email Sent. Check your inbox.";
		
	}
	
	

   
}