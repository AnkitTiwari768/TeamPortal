<?php 

declare(strict_types=1);

namespace App\Web\PMRegistration;

use App\Traits\HasResponses;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Http\Api\V1\PMRegistration\{PMRegistrationService, PMRegistrationRequest};


class PMRegistrationController extends ClientController
{
    private static string $module = 'pm_registration.index';

	public function __construct(
		private PMRegistrationService $service,
	) {
		$this->service = $service;
	}

	/*public function userDataTable(){
		return $this->success($this->service->getPMVUsersData());
	}*/
	
	public function index(): View
	{
		$title = __('Create Your Account');
		$type = ['cancelled_cheque','vishwakarma_form_copy'];
        $documents = $this->service->getDocuments($type);
		crypto_secrets();
		return view('pm_registration.signup', compact('title','documents'))
			->with('crypto_salt', session('crypto_salt'))
			->with('crypto_iv', session('crypto_iv'))
			->with('crypto_key', session('crypto_key'))
			->with('crypto_key_size', session('crypto_key_size'))
			->with('crypto_iterations', session('crypto_iterations'))
			->with('crypto_iterations', session('crypto_iterations'))
			->with('lists', (object) $this->service->getDropdownList())
			->with('categories', $this->service->productCategories());
	}

	public function create(Request $request)
	{
		$validator = Validator::make($request->all(), PMRegistrationRequest::getRules($request), PMRegistrationRequest::messages());
		if ($validator->fails()) {
			return $this->error($validator->errors());
		}
		
		$this->service->store($request->all());
		return $this->success(message: 'Registration Successfully');
	}
	


}