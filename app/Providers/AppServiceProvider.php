<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Discord\Provider as DiscordProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('discord', DiscordProvider::class);
        });

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
            'trash.module.view',
            'trash.restore',
            'trash.delete',
            'form.submission.list',
            'form.submission.review',
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
