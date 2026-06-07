<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPermissions();
    }

    /**
     * Register general permissions defined in PermissionSeeder.
     */
    protected function registerPermissions(): void
    {
        $permissions = [
            'dashboard.module.view',
            'warning.module.view',
            'post.module.view',
            'locution.module.view',
            'radio.module.view',
            'podcast.module.view',
            'marketing.module.view',
            'media.module.view',
            'administration.module.view',
            'log.module.view',
            'locution.start',
            'locution.finish',
            'listener.month.view',
            'listener.month.set',
        ];

        foreach ($permissions as $permission) {
            Gate::define($permission, function (User $user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }
    }
}
