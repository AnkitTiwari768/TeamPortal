<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Http\Api\V1\{
    Department\Department,
    Designation\Designation,
    Country\Country,
    District\District,
    State\State,
    Currency\Currency,
    Role\Role,
    User\User,
	Signup\Signup
};
use App\Observers\{ 
    BaseObserver 
};
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        $modelsToObserve = [Department::class, Designation::class, Country::class, District::class, State::class, Role::class, User::class, Signup::class];
        
        foreach ($modelsToObserve as $model) {
            $model::observe(BaseObserver::class);
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
