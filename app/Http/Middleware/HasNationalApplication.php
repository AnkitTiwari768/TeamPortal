<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HasNationalApplication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (hasRole('applicant')) 
        {
            if (\Session::has('project_type') && ((int) session('project_type')) === \App\Enums\ProductionType::International->value) {
                return $next($request);
            }
    
            return redirect('dashboard');
        }
        
        return $next($request);
    }
}
