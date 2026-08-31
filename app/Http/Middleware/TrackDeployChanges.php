<?php

namespace App\Http\Middleware;

use Closure;
use App\Web\DeployTracker\DeployTracker;
use Illuminate\Support\Facades\Log;

class TrackDeployChanges
{
    public function handle($request, Closure $next)
    {
        try {
            $tracker = new DeployTracker();
            $tracker->track('auto');
        } catch (\Exception $e) {
            Log::error('Middleware DeployTracker failed: ' . $e->getMessage());
        }

        return $next($request); 
    }
}