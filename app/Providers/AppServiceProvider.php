<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set Carbon locale to Spanish
        \Carbon\Carbon::setLocale('es');
        
        // Also set the application locale for consistency
        app()->setLocale('es');

        // Register custom Blade directives for permissions
        $this->registerPermissionDirectives();
    }

    /**
     * Register custom Blade directives for permission checks.
     */
    protected function registerPermissionDirectives()
    {
        // @canPermission('events.create.team')
        Blade::if('canPermission', function ($permission, $team = null) {
            return userCan($permission, $team);
        });

        // @cannotPermission('events.delete.team')
        Blade::if('cannotPermission', function ($permission, $team = null) {
            return userCannot($permission, $team);
        });

        // @hasAnyPermission(['events.create.own', 'events.create.team'])
        Blade::if('hasAnyPermission', function ($permissions, $team = null) {
            return userHasAnyPermission($permissions, $team);
        });

        // @hasAllPermissions(['events.view.team', 'events.update.team'])
        Blade::if('hasAllPermissions', function ($permissions, $team = null) {
            return userHasAllPermissions($permissions, $team);
        });
    }
}
