<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth; 
use App\Http\Controllers\ClientController; 
use App\Http\Api\V1\UserProfile\UserProfileService as Service;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Rules\ValidateAuthPassword; 
use App\Rules\NotInPreviousPasswords;

use Illuminate\Support\Facades\Password;

final class ChangePasswordController extends ClientController 
{   
    protected $service;
    private $module_url = 'users.index';

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        crypto_secrets(); 
        $id = auth()->user()->id;       
        $module_url=$this->module_url;
        return view('auth.changePassword', compact('id','module_url'))
            ->with('title', __('message.change_password'))
            ->with('is_profile', true)
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    } 

    public function changePassword(Request $request)
    {  
        try 
        {
            $request->merge([
                'current_password' => crypto_decrypt($request->current_password),
                'password' => crypto_decrypt($request->password),
                'password_confirmation' => crypto_decrypt($request->password_confirmation),
            ]); 
            
            $validator = Validator::make($request->all(), [
            'current_password' => ['required', new ValidateAuthPassword()],
            'password' => ['required', 'confirmed',Rules\Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised(), new NotInPreviousPasswords(auth()->user())],
            'password_confirmation' => 'required',
            ]); 

            if ($validator->fails()) 
                return $this->error($validator->errors()); 
         
            //dd($validator->validated());
            $id=auth()->user()->id; 
            $this->service->updatePassword($validator->validated(),$id);
            
            return $this->updated();
                
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
 

}
