<?php 

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Http\Services\ClientService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Request; 

class AuthClient extends ClientService
{
    public function authenticate(array $request)
    {
        $response = $this->post('login', $request, 0);
       
        if ($response->status) 
        {
            session('auth', $response->data);
        }
    }

    public function logout()
    {
        return $this->post('logout');
    }
}