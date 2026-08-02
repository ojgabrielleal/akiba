<?php

use Illuminate\Support\Facades\Route;

// Public controllers
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountCallbackController;
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountLogoutController;
use App\Http\Controllers\Api\External\OAuthAccount\OAuthAccountRedirectController;
use App\Http\Controllers\Public\FormSubmissionController;
use App\Http\Controllers\Public\OAuthAccountController;
use App\Http\Controllers\Private\Invokes\PollVoteController;
use App\Http\Controllers\Public\Invokes\StorePostCommentController;
use App\Http\Controllers\Public\Invokes\StorePostReactionController;
use App\Http\Controllers\Public\Invokes\TogglePostLikeController;
use App\Http\Controllers\Public\Pages\EditorialPageController;
use App\Http\Controllers\Public\Pages\ContactPageController;
use App\Http\Controllers\Public\Pages\HomePageController;
use App\Http\Controllers\Public\Pages\MediaPageController;
use App\Http\Controllers\Public\Pages\RadioPageController;
use App\Http\Controllers\Public\Pages\ReadPageController;
use App\Http\Controllers\Public\Pages\SearchPageController;
use App\Http\Controllers\Public\Pages\TeamPageController;

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

Route::middleware(['oauth.resolve', 'inertia'])->group(function () {
    Route::get('/contato', [ContactPageController::class, 'render'])
        ->name('contact');

    Route::post('/form-submissions', [FormSubmissionController::class, 'store'])
        ->name('form-submissions.store');
});

Route::middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
    Route::get('/news', [EditorialPageController::class, 'news'])
        ->name('news');

    Route::get('/colunas', [EditorialPageController::class, 'columns'])
        ->name('columns');

    Route::get('/reviews', [EditorialPageController::class, 'reviews'])
        ->name('reviews');

    Route::get('/equipe', [TeamPageController::class, 'render'])
        ->name('team');

    Route::get('/radio', [RadioPageController::class, 'render'])
        ->name('radio');

    Route::get('/midias', [MediaPageController::class, 'render'])
        ->name('media');

    Route::get('/buscar', [SearchPageController::class, 'render'])
        ->name('search');

    Route::get('/materia/{slug}', [ReadPageController::class, 'render'])
        ->name('post.read');

    Route::post('/materia/{post:slug}/reaction', StorePostReactionController::class)
        ->middleware('oauth')
        ->name('post.reaction.store');

    Route::post('/materia/{post:slug}/like', TogglePostLikeController::class)
        ->name('post.like.toggle');

    Route::post('/materia/{post:slug}/comment', StorePostCommentController::class)
        ->middleware('oauth')
        ->name('post.comment.store');

    Route::post('/poll/option/{option:uuid}/vote', [PollVoteController::class, '__invoke'])
        ->name('poll.option.vote');

    Route::get('/review/{slug}', [ReadPageController::class, 'render'])
        ->name('review.read');

    Route::get('/event/{slug}', [ReadPageController::class, 'render'])
        ->name('event.read');

    Route::get('/evento/{slug}', [ReadPageController::class, 'render'])
        ->name('event.read.legacy');
});

Route::prefix("site")->middleware(['oauth.resolve', 'inertia', 'auth'])->group(function () {
    Route::controller(HomePageController::class)->group(function () {
        Route::get('', 'render')->name('home');
    });

    Route::patch('profile', [OAuthAccountController::class, 'update'])
        ->middleware('oauth')
        ->name('oauth.profile.update');
});
        
