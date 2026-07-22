<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
            'report.module.view',
            'locution.start',
            'locution.finish',
        ];

        foreach ($permissions as $permission) {
            Gate::define($permission, function (User $user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }
    }
}
