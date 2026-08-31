<?php 

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends ClientController
{
    public function __construct(private AuthClient $client)
    {
        $this->client = $client;
    }

    public function index()
    {
        return view('auth.login')
		    ->with('title', __('message.login'));
    }

    public function authenticate(Request $request)
    {
        $this->client->authenticate($request->all());
        $request->session()->regenerate();
        return redirect('/dashboard');
    }

    public function destroy(Request $request)
    {
        $this->client->logout();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken(); 
        return redirect('/login');
    }
}