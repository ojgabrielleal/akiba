<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\User;
use App\Policies\CommentPolicy;
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
        Gate::policy(Comment::class, CommentPolicy::class);
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
            'comment.module.view',
            'trash.restore',
            'trash.delete',
            'comment.list',
            'comment.view',
            'comment.approve',
            'comment.hide',
            'comment.restore',
            'comment.delete',
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
