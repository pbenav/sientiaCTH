<?php

namespace App\Providers;

use App\Models\EventType;
use App\Models\Holiday;
use App\Models\Team;
use App\Models\User;
use App\Policies\EventTypePolicy;
use App\Policies\HolidayPolicy;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        EventType::class => EventTypePolicy::class,
        Holiday::class => HolidayPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('viewSecurityPanel', function (User $user) {            
                if (Auth::user()->isTeamAdmin()) {
                    return true;
                }
            return false;
        });
    }
}
