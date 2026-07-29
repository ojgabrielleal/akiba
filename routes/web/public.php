<?php

use Illuminate\Support\Facades\Route;

// Public controllers
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountCallbackController;
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountRedirectController;
use App\Http\Controllers\Public\OAuthAccountController;
use App\Http\Controllers\Public\Invokes\StorePostCommentController;
use App\Http\Controllers\Public\Invokes\StorePostReactionController;
use App\Http\Controllers\Public\Pages\EditorialPageController;
use App\Http\Controllers\Public\Pages\HomePageController;
use App\Http\Controllers\Public\Pages\ReadPageController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/oauth/{provider}/redirect', OAuthAccountRedirectController::class)
    ->name('oauth.redirect');

Route::get('/oauth/{provider}/callback', OAuthAccountCallbackController::class)
    ->name('oauth.callback');

Route::middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
    Route::get('/news', [EditorialPageController::class, 'news'])
        ->name('news');

    Route::get('/colunas', [EditorialPageController::class, 'columns'])
        ->name('columns');

    Route::get('/materia/{slug}', [ReadPageController::class, 'render'])
        ->name('post.read');

    Route::post('/materia/{post:slug}/reaction', StorePostReactionController::class)
        ->middleware('oauth')
        ->name('post.reaction.store');

    Route::post('/materia/{post:slug}/comment', StorePostCommentController::class)
        ->middleware('oauth')
        ->name('post.comment.store');

    Route::get('/review/{slug}', [ReadPageController::class, 'render'])
        ->name('review.read');

    Route::get('/evento/{slug}', [ReadPageController::class, 'render'])
        ->name('event.read');
});

Route::prefix("site")->middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
    Route::controller(HomePageController::class)->group(function () {
        Route::get('', 'render')->name('home');
    });

    Route::patch('profile', [OAuthAccountController::class, 'update'])
        ->middleware('oauth')
        ->name('oauth.profile.update');
});
        
