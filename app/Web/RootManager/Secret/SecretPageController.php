<?php
namespace App\Web\RootManager\Secret;

use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use App\Web\RootManager\Secret\SecretCodeRequest;
use App\Web\RootManager\Secret\SecretCodeService;

class SecretPageController extends ClientController
{   
    protected $secretCodeService;

    public function __construct(SecretCodeService $secretCodeService)
    {
        $this->secretCodeService = $secretCodeService;
    }
    
    public function index()
    {
        // If already authenticated, redirect to dashboard
        if ($this->secretCodeService->isAuthenticated()) {
            return redirect()->route('secret.dashboard');
        }
        
        return view('root-manager.secret.secret_page', [
            'title' => 'Secret Login'
        ]);
    }
    
    public function dashboard()
    {
        // Check authentication
        if (!$this->secretCodeService->isAuthenticated()) {
            return redirect()->route('secret.page');
        }
        
        return view('root-manager.secret.secret-dashboard', [
            'title' => 'Secret Dashboard'
        ]);
    }

    public function verify(SecretCodeRequest $request)
    {
        // Verify the entered code
        $result = $this->secretCodeService->verifyCode($request->code_value);
        return response()->json($result);
    }

    public function logout(Request $request)
    {
        $result = $this->secretCodeService->logout();
        return response()->json($result);
    }
}