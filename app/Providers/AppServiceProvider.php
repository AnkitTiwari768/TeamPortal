<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Web\DeployTracker\DeployTracker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->runDeployTracker();
    }

    private function runDeployTracker(): void
    {
        try {
            $lastRun = Cache::get('deploy_tracker_last_run', 0);
            $now = time();
            if (($now - $lastRun) < 2) {
                return;
            }
            Cache::put('deploy_tracker_last_run', $now);

            $tracker = new DeployTracker();
            $tracker->track('auto-server');

            Log::info('✅ DeployTracker ran');

        } catch (\Throwable $e) {
            Log::error('❌ Auto tracker error: ' . $e->getMessage());
        }
    }
}