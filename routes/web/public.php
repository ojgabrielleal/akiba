<?php

use Illuminate\Support\Facades\Route;

// Public controllers
use App\Http\Controllers\Api\OAuth\OAuthCallbackController;
use App\Http\Controllers\Api\OAuth\OAuthRedirectController;
use App\Http\Controllers\Public\HomeController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/oauth/{provider}/redirect', OAuthRedirectController::class)
    ->name('oauth.redirect');

Route::get('/oauth/{provider}/callback', OAuthCallbackController::class)
    ->name('oauth.callback');

Route::prefix("site")->middleware(['inertia', 'auth'])->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get('', 'render')->name('home');
    });
});
        
