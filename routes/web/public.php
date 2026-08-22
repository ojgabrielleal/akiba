<?php

use Illuminate\Support\Facades\Route;

// Public controllers
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountCallbackController;
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountLogoutController;
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountRedirectController;
use App\Http\Controllers\Public\EditorialController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MediaController;
use App\Http\Controllers\Public\PlayerController;
use App\Http\Controllers\Public\PushNotificationController;
use App\Http\Controllers\Public\RadioController;
use App\Http\Controllers\Public\ReadController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\TeamController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/oauth/{provider}/redirect', OAuthAccountRedirectController::class)
    ->name('oauth.redirect');

Route::get('/oauth/{provider}/callback', OAuthAccountCallbackController::class)
    ->name('oauth.callback');

Route::post('/oauth/logout', OAuthAccountLogoutController::class)
    ->middleware('oauth.resolve')
    ->name('oauth.logout');

Route::post('/push-notification', [PushNotificationController::class, 'storePushNotification'])
    ->name('push-notification.store');

Route::middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
    Route::get('/contato', [ContactController::class, 'render'])
        ->name('contact');

    Route::post('/form-submissions', [ContactController::class, 'storeFormSubmission'])
        ->name('form-submissions.store');

    Route::post('/song-request', [PlayerController::class, 'storeSongRequest'])
        ->middleware('oauth')
        ->name('player.song-request.store');
});

Route::middleware(['oauth.resolve', 'inertia'])->group(function () {
    Route::get('/news', [EditorialController::class, 'news'])
        ->name('news');

    Route::get('/colunas', [EditorialController::class, 'columns'])
        ->name('columns');

    Route::get('/equipe', [TeamController::class, 'render'])
        ->name('team');

    Route::get('/radio', [RadioController::class, 'render'])
        ->name('radio');

    Route::get('/midias', [MediaController::class, 'render'])
        ->name('media');

    Route::get('/buscar', [SearchController::class, 'render'])
        ->name('search');

    Route::get('/materia/{slug}', [ReadController::class, 'render'])
        ->name('post.read');

    Route::post('/materia/{post:slug}/reaction', [ReadController::class, 'storeReaction'])
        ->middleware('oauth')
        ->name('post.reaction.store');

    Route::post('/materia/{post:slug}/like', [ReadController::class, 'toggleLike'])
        ->name('post.like.toggle');

    Route::post('/materia/{post:slug}/comment', [ReadController::class, 'storeComment'])
        ->middleware('oauth')
        ->name('post.comment.store');

    Route::patch('/materia/{post:slug}/comment/{comment}', [ReadController::class, 'updateComment'])
        ->middleware('oauth')
        ->name('post.comment.update');

    Route::delete('/materia/{post:slug}/comment/{comment}', [ReadController::class, 'deleteComment'])
        ->middleware('oauth')
        ->name('post.comment.delete');

    Route::patch('/materia/{post:slug}/comment/{comment}/approve', [ReadController::class, 'approveComment'])
        ->middleware('auth')
        ->name('post.comment.approve');

    Route::patch('/materia/{post:slug}/comment/{comment}/hide', [ReadController::class, 'hideComment'])
        ->middleware('auth')
        ->name('post.comment.hide');

    Route::patch('/materia/{post:slug}/comment/{comment}/restore', [ReadController::class, 'restoreComment'])
        ->middleware('auth')
        ->name('post.comment.restore');

    Route::delete('/materia/{post:slug}/comment/{comment}/moderate', [ReadController::class, 'destroyComment'])
        ->middleware('auth')
        ->name('post.comment.destroy');

    Route::post('/poll/option/{option:uuid}/vote', [MediaController::class, 'votePollOption'])
        ->name('poll.option.vote');

    Route::get('/review/{slug}', [ReadController::class, 'render'])
        ->name('review.read');

    Route::get('/event/{slug}', [ReadController::class, 'render'])
        ->name('event.read');

    Route::get('/evento/{slug}', [ReadController::class, 'render'])
        ->name('event.read.legacy');
});

Route::prefix("site")->middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get('', 'render');
    });

    Route::patch('profile', [HomeController::class, 'updateOAuthAccountProfile'])
        ->middleware('oauth')
        ->name('oauth.profile.update');
});

Route::prefix("site")->middleware(['oauth.resolve', 'inertia'])->group(function () {
    Route::patch('member-profile', [HomeController::class, 'updateMemberProfile'])
        ->name('member.profile.update');

    Route::post('member-logout', [HomeController::class, 'logoutMemberProfile'])
        ->name('member.profile.logout');
});
        
