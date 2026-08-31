<?php 

declare(strict_types=1);

namespace App\Web\CA;

use App\Models\User;
use App\Models\TeamSnpcaMapping;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Web\CA\CAValidation as Validation;

class CAController extends ClientController
{
	
    private static string $module = 'ca-user.index';

	public function __construct(private CAService $service)
    {
        $this->service = $service;
    }
	
    public function index(): View
    { 
        guard('ca-view');
        $currentUserId = auth()->id();

        $mappings = TeamSnpcaMapping::where('snp_user_id', $currentUserId)
            ->with('caUser')
            ->get();

        $caUsers = $mappings->map(fn($mapping) => $mapping->caUser);

        return view('ca.index')
            ->with('title', __('CA User'))
            ->with('caUsers', $caUsers);
    }

	
    public function datalist(): mixed
    {
        guard('ca-view');
        try{            
        //    $result= $this->service->getUsers();  
        //    return $this->success($result);
        }catch(\Throwable $e){
            return $this->handleException($e);
        }
    } 

    public function create(): View
    {
        guard('ca-create');
        crypto_secrets(); 
        return view('ca.form')
            ->with('title', __('Add CA'))
            ->with('module_url', self::$module)
           // ->with('details', (object) $this->service->getDetails())
			->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }

    public function store(Request $request) 
    {   
        try{        
			
			$request->merge([
                'password' => crypto_decrypt($request->password),
                'password_confirmation' => crypto_decrypt($request->password_confirmation),
            ]);
            
            $validator = Validation::validate($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
           
            $result = $this->service->store($validator->validated());
            return $this->created($result);
        }catch(\Throwable $e){
            return $this->handleException($e);
        } 
    }

    public function edit(string $id): View
    {
        crypto_secrets();

        $user = User::findOrFail($id);

        return view('ca.form')
            ->with('title', __('Edit CA'))
            ->with('module_url', self::$module)
            ->with('row', $user->toArray())
            ->with('id', $id)
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }

    public function update(Request $request, string $id): mixed
    {   
        guard('ca-create');
        try{
			
			$validator = Validation::validate($request->all(), $id);
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
			
             $result = $this->service->store($validator->validated(),$id);
            return $this->updated();
        }catch(\Throwable $e){
            return $this->handleException($e);
        }
    }

}