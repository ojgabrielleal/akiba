<?php

use Illuminate\Support\Facades\Route;

// Public controllers
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountCallbackController;
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountRedirectController;
use App\Http\Controllers\Public\OAuthAccountController;
use App\Http\Controllers\Public\Pages\HomePageController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/oauth/{provider}/redirect', OAuthAccountRedirectController::class)
    ->name('oauth.redirect');

Route::get('/oauth/{provider}/callback', OAuthAccountCallbackController::class)
    ->name('oauth.callback');

Route::prefix("site")->middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
    Route::controller(HomePageController::class)->group(function () {
        Route::get('', 'render')->name('home');
    });

    Route::patch('profile', [OAuthAccountController::class, 'update'])
        ->middleware('oauth')
        ->name('oauth.profile.update');
});
        
