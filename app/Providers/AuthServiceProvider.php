<?php

namespace App\Providers;

use App\Models\EventType;
use App\Models\Team;
use App\Policies\EventTypePolicy;
use App\Policies\TeamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
            foreach ($user->allTeams() as $team) {
                if ($user->hasTeamRole($team, 'admin')) {
                    return true;
                }
            }
            return false;
        });
    }
}
