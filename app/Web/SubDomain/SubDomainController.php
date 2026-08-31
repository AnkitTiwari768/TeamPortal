<?php 
declare(strict_types=1);
namespace App\Web\SubDomain;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController; 


class SubDomainController extends ClientController
{
    private static string $module = 'sub-domains.index';

    public function __construct(private SubDomainService $service){}

    public function index(): View
    {
        //guard(config('permissions.department-view'));        
        $title = __('SubDomain List');        
        return view('subdomains.index', compact('title'));  
    }

    public function getSubDomain()
    {
        //guard(config('permissions.department-view'));
        
        return $this->success($this->service->getSubDomain());
    }
    

    public function create(): View
    {
        //guard(config('permissions.department-create'));        
        $title = __('Add SubDomain');
        $module_url = static::$module;
        $lists = (object) $this->service->getDropdownList();
        //echo '<pre>';print_r($lists); die();
        return view('subdomains.form', compact('title', 'module_url','lists'));
    }

    public function createSubDomain(Request $request) 
    {   
        //guard(config('permissions.department-create'));
        $validator = Validator::make($request->all(), SubDomainRequest::getRules());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
        return $this->created(
            $this->service->storeSubDomain($validator->validated())
        );
    }

    public function edit(string $id): View
    {
        //guard(config('permissions.department-update'));        
        $title = __('Edit SubDomain');
        $module_url = static::$module;        
        $row = $this->service->getSubDomainbyId($id);
        $lists = (object) $this->service->getDropdownList();
        return view('subdomains.form', compact('title', 'module_url', 'row', 'id','lists'));
            
    }

    public function updateSubDomain(Request $request, string $id) 
    {   
        //guard(config('permissions.department-update'));
        
        $validator = Validator::make($request->all(), SubDomainRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->service->storeSubDomain($validator->validated(), $id)
        );
    }
}