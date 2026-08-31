<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XssClean
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */ 

    /*public function handle(Request $request, Closure $next)
    {
        $input = $request->all(); 
        array_walk_recursive($input, function(&$input) { 
            $input = strip_tags($input); 
            // Encode special characters except '&' and '-'
            $input = preg_replace_callback(
                '/[^&-]+/', 
                function($matches) {
                    return htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8');
                },
                $input
            );
        });

        $request->merge($input);

        return $next($request);
    }*/

    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            // Step 1: Remove HTML tags (XSS prevention)
            $value = strip_tags($value);

            // Step 2: Encode special characters (optional hardening)
            $value = preg_replace_callback(
                '/[^&-]+/',
                function ($matches) {
                    return htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8');
                },
                $value
            );

            // Step 3: Prevent CSV injection by neutralizing dangerous first characters
            if (preg_match('/^(\=|\+|\-|\@)/', $value)) {
                $value = "'" . $value; // Prefix with single quote
            }
        });

        $request->merge($input);

        return $next($request);
    }


}
