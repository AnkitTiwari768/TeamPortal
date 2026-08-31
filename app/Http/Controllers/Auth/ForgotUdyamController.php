<?php 
  
namespace App\Http\Controllers\Auth; 
  
use App\Http\Controllers\ClientController;
use Illuminate\Http\Request; 

class ForgotUdyamController extends ClientController
{
    public function __construct()
    {}
     
    public function index()
    {
        crypto_secrets();
       return view('auth.forgetUdyam')
        ->with('crypto_salt', session('crypto_salt'))
        ->with('crypto_iv', session('crypto_iv'))
        ->with('crypto_key', session('crypto_key'))
        ->with('crypto_key_size', session('crypto_key_size'))
        ->with('crypto_iterations', session('crypto_iterations'))
       ->with('title', __('message.forgot_udyam_number'));
    }

}