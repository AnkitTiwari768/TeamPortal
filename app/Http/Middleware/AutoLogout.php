<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AutoLogout
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
			
			$user = auth()->user();
			DB::table('users')->where('email', $user->email)->update(['last_session' => NULL]);
			
            $sessionId = $request->session()->getId();
            // Check last activity
            if (session()->has('lastActivityTime')) {
                $inactiveTime = now()->diffInMinutes(session('lastActivityTime'));

                if ($inactiveTime > config('session.lifetime')) {
                    // ✅ Logout user
                    Auth::logout();

                    // ✅ Invalidate + flush Laravel session
                    $request->session()->invalidate();
                    $request->session()->flush();
                    $request->session()->regenerateToken();

                    // ✅ Delete session from storage
                    if (config('session.driver') === 'database') {
                        DB::table('sessions')->where('id', $sessionId)->delete();
                    } elseif (config('session.driver') === 'file') {
                        $path = storage_path('framework/sessions/' . $sessionId);
                        if (file_exists($path)) {
                            @unlink($path);
                        }
                    }

                    return redirect('/login')->with('message', 'Session expired due to inactivity.');
                }
            }

            // Update last activity
            session(['lastActivityTime' => now()]);
        }

        return $next($request);
    }
}
