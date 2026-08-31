<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Services\ClientService;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('clientservice',function() {
            return new ClientService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
