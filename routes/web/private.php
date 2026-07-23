<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Private\Pages\AdministrationPageController;
use App\Http\Controllers\Private\Pages\DashboardPageController;
use App\Http\Controllers\Private\Pages\LocutionPageController;
use App\Http\Controllers\Private\Pages\LoginPageController;
use App\Http\Controllers\Private\Pages\ReportsPageController;
use App\Http\Controllers\Private\Pages\MediaPageController;
use App\Http\Controllers\Private\Pages\PodcastPageController;
use App\Http\Controllers\Private\Pages\PostPageController;
use App\Http\Controllers\Private\Pages\ProfilePageController;
use App\Http\Controllers\Private\Pages\RadioPageController;
use App\Http\Controllers\Private\Pages\RepositoryPageController;

use App\Http\Controllers\Private\Invokes\ConfirmActivityParticipantController;
use App\Http\Controllers\Private\Invokes\CompleteTaskController;
use App\Http\Controllers\Private\Invokes\DeactivatePodcastController;
use App\Http\Controllers\Private\Invokes\DeactivatePollController;
use App\Http\Controllers\Private\Invokes\DeactivatePostController;
use App\Http\Controllers\Private\Invokes\DeactivateProgramController;
use App\Http\Controllers\Private\Invokes\DeactivateRepositoryController;
use App\Http\Controllers\Private\Invokes\DeactivateTaskController;
use App\Http\Controllers\Private\Invokes\DeactivateUserController;
use App\Http\Controllers\Private\Invokes\FinishLocutionController;
use App\Http\Controllers\Private\Invokes\LoginController;
use App\Http\Controllers\Private\Invokes\LogoutController;
use App\Http\Controllers\Private\Invokes\MarkSongRequestAsCanceledController;
use App\Http\Controllers\Private\Invokes\MarkSongRequestAsPlayedController;
use App\Http\Controllers\Private\Invokes\MarkTaskToReviewController;
use App\Http\Controllers\Private\Invokes\PollVoteController;
use App\Http\Controllers\Private\Invokes\RefreshMusicRankingController;
use App\Http\Controllers\Private\Invokes\StartLocutionController;
use App\Http\Controllers\Private\Invokes\ToggleSongRequestBoxStatusController;
use App\Http\Controllers\Private\ActivityController;
use App\Http\Controllers\Private\CalendarController;
use App\Http\Controllers\Private\ListenerGalleryController;
use App\Http\Controllers\Private\ListenerMonthController;
use App\Http\Controllers\Private\MusicController;
use App\Http\Controllers\Private\PodcastController;
use App\Http\Controllers\Private\PollController;
use App\Http\Controllers\Private\PostController;
use App\Http\Controllers\Private\ProfileController;
use App\Http\Controllers\Private\ProgramController;
use App\Http\Controllers\Private\RepositoryController;
use App\Http\Controllers\Private\RoleController;
use App\Http\Controllers\Private\TaskController;
use App\Http\Controllers\Private\UserController;

/*
|--------------------------------------------------------------------------
| Private routes
|--------------------------------------------------------------------------
*/
Route::prefix('panel')->middleware(['inertia'])->group(function () {
    Route::controller(LoginPageController::class)->group(function () {
        Route::get('', 'render')->name('login');
        Route::post('auth', [LoginController::class, '__invoke']);
    });

    Route::middleware(['auth', 'authenticated.user'])->group(function () {
        Route::post('logout', [LogoutController::class, '__invoke'])->name('logout');

        Route::prefix('dashboard')->controller(DashboardPageController::class)->group(function () {
            Route::get('', 'render')->name('panel.dashboard');
            Route::prefix('activity')->group(function () {
                Route::post('{activity:uuid}/confirm', [ConfirmActivityParticipantController::class, '__invoke']);
            });
            Route::prefix('task')->group(function () {
                Route::post('{task:uuid}/review', [MarkTaskToReviewController::class, '__invoke']);
            });
        });

        Route::prefix('post')->controller(PostPageController::class)->group(function () {
            Route::get('', 'render')->name('panel.post');
            Route::post('', [PostController::class, 'store']);
            Route::patch('{post:uuid}', [PostController::class, 'update']);
            Route::get('{post:uuid}', [PostController::class, 'show']);
            Route::patch('{post:uuid}/deactivate', [DeactivatePostController::class, '__invoke']);
        });

        Route::prefix('locution')->controller(LocutionPageController::class)->group(function () {
            Route::post('start/{program:uuid}', [StartLocutionController::class, '__invoke']);
            Route::patch('finish', [FinishLocutionController::class, '__invoke']);
            Route::prefix('song-request')->group(function () {
                Route::patch('{songRequest:uuid}/played', [MarkSongRequestAsPlayedController::class, '__invoke']);
                Route::patch('{songRequest:uuid}/canceled', [MarkSongRequestAsCanceledController::class, '__invoke']);
                Route::patch('box/toggle', [ToggleSongRequestBoxStatusController::class, '__invoke']);
            });
            Route::get('', 'render')->name('panel.locution');
        });

        Route::prefix('radio')->controller(RadioPageController::class)->group(function () {
            Route::prefix('program')->group(function () {
                Route::post('', [ProgramController::class, 'store']);
                Route::patch('{program:uuid}', [ProgramController::class, 'update']);
                Route::patch('{program:uuid}/deactivate', [DeactivateProgramController::class, '__invoke']);
            });
            Route::prefix('music')->group(function () {
                Route::post('ranking/refresh', [RefreshMusicRankingController::class, '__invoke']);
                Route::patch('{music:uuid}', [MusicController::class, 'update']);
            });
            Route::prefix('listener-month')->group(function () {
                Route::post('', [ListenerMonthController::class, 'store']);
            });
            Route::get('', 'render')->name('panel.radio');
        });

        Route::prefix('podcast')->controller(PodcastPageController::class)->group(function () {
            Route::get('', 'render')->name('panel.podcast');
            Route::post('', [PodcastController::class, 'store']);
            Route::patch('{podcast:uuid}', [PodcastController::class, 'update']);
            Route::patch('{podcast:uuid}/deactivate', [DeactivatePodcastController::class, '__invoke']);
            Route::get('{podcast:uuid}', [PodcastController::class, 'show']);
        });

        Route::prefix('marketing')->controller(RepositoryPageController::class)->group(function () {
            Route::prefix('repository')->group(function () {
                Route::post('', [RepositoryController::class, 'store']);
                Route::get('{repository:uuid}', [RepositoryController::class, 'show']);
                Route::patch('{repository:uuid}', [RepositoryController::class, 'update']);
                Route::patch('{repository:uuid}/deactivate', [DeactivateRepositoryController::class, '__invoke']);
            });
            Route::get('', 'render')->name('panel.marketing');
        });

        Route::prefix('media')->controller(MediaPageController::class)->group(function () {
            Route::prefix('listener-gallery')->group(function () {
                Route::post('', [ListenerGalleryController::class, 'store']);
                Route::get('{listenerGallery:uuid}', [ListenerGalleryController::class, 'show']);
                Route::patch('{listenerGallery:uuid}', [ListenerGalleryController::class, 'update']);
                Route::delete('{listenerGallery:uuid}', [ListenerGalleryController::class, 'destroy']);
            });
            Route::prefix('poll')->group(function () {
                Route::post('', [PollController::class, 'store']);
                Route::patch('{poll:uuid}', [PollController::class, 'update']);
                Route::patch('{poll:uuid}/deactivate', [DeactivatePollController::class, '__invoke']);
                Route::post('option/{option:uuid}/vote', [PollVoteController::class, '__invoke']);
            });
            Route::get('', 'render')->name('panel.media');
        });

        Route::prefix('administration')->controller(AdministrationPageController::class)->group(function () {
            Route::prefix('user')->group(function () {
                Route::post('', [UserController::class, 'store']);
                Route::get('{user:uuid}', [UserController::class, 'show']);
                Route::patch('{user:uuid}/deactivate', [DeactivateUserController::class, '__invoke']);
                Route::patch('{user:uuid}', [UserController::class, 'updateAccess']);
            });
            Route::prefix('role')->group(function () {
                Route::post('', [RoleController::class, 'store']);
                Route::get('{role:uuid}', [RoleController::class, 'show']);
                Route::patch('{role:uuid}', [RoleController::class, 'update']);
                Route::delete('{role:uuid}', [RoleController::class, 'destroy']);
            });
            Route::prefix('calendar')->group(function () {
                Route::post('', [CalendarController::class, 'store']);
                Route::get('{calendar:uuid}', [CalendarController::class, 'show']);
                Route::patch('{calendar:uuid}', [CalendarController::class, 'update']);
            });
            Route::prefix('activity')->group(function () {
                Route::post('', [ActivityController::class, 'store']);
                Route::get('{activity:uuid}', [ActivityController::class, 'show']);
                Route::patch('{activity:uuid}', [ActivityController::class, 'update']);
            });
            Route::prefix('task')->group(function () {
                Route::get('{task:uuid}', [TaskController::class, 'show']);
                Route::post('', [TaskController::class, 'store']);
                Route::patch('{task:uuid}', [TaskController::class, 'update']);
                Route::patch('{task:uuid}/complete', [CompleteTaskController::class, '__invoke']);
                Route::patch('{task:uuid}/deactivate', [DeactivateTaskController::class, '__invoke']);
            });
            Route::get('', 'render')->name('panel.administration');
        });
        Route::prefix('reports')->controller(ReportsPageController::class)->group(function () {
            Route::get('', 'render')->name('panel.reports');
        });
        Route::prefix('profile')->controller(ProfilePageController::class)->group(function () {
            Route::patch('{user:uuid}', [ProfileController::class, 'update']);
            Route::get('{user:uuid}', 'render')->name('panel.profile');
        });
    });
});
