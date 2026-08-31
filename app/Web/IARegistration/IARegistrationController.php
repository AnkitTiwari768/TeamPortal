<?php 

declare(strict_types=1);

namespace App\Web\IARegistration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Domain\IARegistration\ViewIARegistration;
use App\Web\ApplicationWorkflow\ApplicationWorkflowService;


class IARegistrationController extends ClientController
{
    private static string $module = 'signup.index';

	public function __construct(private IAService $service)
    {
        $this->service = $service;
    }
	
    public function index(): View
    {
        
        $title = __('IA Registration');
        $entity_type = $this->service->getEntityType();
        $documents = $this->service->getClaimDocuments('upload-authorization-certificate-document');
        $editableFields = [
            'organization_name',
            'number_of_members',
            'contact_number',
            'contact_person_name',
            'contact_person_phone',
            'contact_person_email',
            'complete_address',
        ];
        return view('ia-registration.registration', compact('title','entity_type','documents','editableFields'))
				->with('lists', (object) $this->service->getDropdownList());
    }

    public function pendingIAList(): View
    {
        $title = "Association Pending List";
        return view('ia-registration.index',compact('title'));
    }

    public function viewIAPage($id)
    {
        $title = "Association View Detail";
        $data =  app(ViewIARegistration::class)->execute($id)->toArray(request());
        //dd($data);
        return view('ia-registration.detail',compact('title','data'));
    }

    public function verifiedIAList()
    {
        $title = "Association Verified List";
        return view('ia-registration.verified-ia-list',compact('title'));
    }







}