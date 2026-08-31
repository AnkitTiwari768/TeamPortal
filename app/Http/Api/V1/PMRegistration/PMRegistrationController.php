<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\PMRegistration;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PMRegistrationController extends ApiController
{
    public function __construct(private PMRegistrationService $service,) {
	    $this->service = $service;
	}

    public function userDataTable(){
		return $this->success($this->service->getPMVUsersData());
	}
    

    public function pmvUserDatail($id){
		$title = __('PM Vishwakarma User View');
		$module_url = 'pmv-user';
		$data = (array) $this->service->getPMVUserDatail($id);
		return view('pm_registration.detail', compact('title','data','module_url'));
	}

    public function pmvUserList(){
		$title = __('PM Vishwakarma Users');
		return view('pm_registration.index', compact('title'));
	}
	
	public function getRegistrationCount() 
	{
			 return $this->success(
			$this->service->getRegistrationCountData(),
			'Data Retrive Successfully'
    	);
	}
   
}