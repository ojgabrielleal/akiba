<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Private\LoginPageController;
use App\Http\Controllers\Private\AdministrationPageController;
use App\Http\Controllers\Private\LocutionPageController;
use App\Http\Controllers\Private\DashboardPageController;
use App\Http\Controllers\Private\PostPageController;
use App\Http\Controllers\Private\RadioPageController;
use App\Http\Controllers\Private\PodcastPageController;
use App\Http\Controllers\Private\RepositoryPageController;
use App\Http\Controllers\Private\MediaPageController;
use App\Http\Controllers\Private\LogsPageController;
use App\Http\Controllers\Private\ProfilePageController;

use App\Http\Controllers\Private\Auth\LoginController;
use App\Http\Controllers\Private\Auth\LogoutController;
use App\Http\Controllers\Private\Administration\Activity\ShowActivityController;
use App\Http\Controllers\Private\Administration\Activity\StoreActivityController;
use App\Http\Controllers\Private\Administration\Activity\UpdateActivityController;
use App\Http\Controllers\Private\Administration\Calendar\ShowCalendarController;
use App\Http\Controllers\Private\Administration\Calendar\StoreCalendarController;
use App\Http\Controllers\Private\Administration\Calendar\UpdateCalendarController;
use App\Http\Controllers\Private\Administration\Role\DestroyRoleController;
use App\Http\Controllers\Private\Administration\Role\ShowRoleController;
use App\Http\Controllers\Private\Administration\Role\StoreRoleController;
use App\Http\Controllers\Private\Administration\Role\UpdateRoleController;
use App\Http\Controllers\Private\Administration\Task\ShowTaskController;
use App\Http\Controllers\Private\Administration\Task\StoreTaskController;
use App\Http\Controllers\Private\Administration\Task\UpdateTaskController;
use App\Http\Controllers\Private\Administration\User\DeactivateUserController;
use App\Http\Controllers\Private\Administration\User\ShowUserController;
use App\Http\Controllers\Private\Administration\User\StoreUserController;
use App\Http\Controllers\Private\Administration\User\UpdateUserAccessController;
use App\Http\Controllers\Private\Dashboard\Activity\ConfirmActivityParticipantController;
use App\Http\Controllers\Private\Dashboard\Task\MarkTaskToReviewController;
use App\Http\Controllers\Private\Locution\FinishLocutionController;
use App\Http\Controllers\Private\Locution\MarkSongRequestAsCanceledController;
use App\Http\Controllers\Private\Locution\MarkSongRequestAsPlayedController;
use App\Http\Controllers\Private\Locution\StartLocutionController;
use App\Http\Controllers\Private\Locution\ToggleSongRequestBoxStatusController;
use App\Http\Controllers\Private\Marketing\Repository\DeactivateRepositoryController;
use App\Http\Controllers\Private\Marketing\Repository\ShowRepositoryController;
use App\Http\Controllers\Private\Marketing\Repository\StoreRepositoryController;
use App\Http\Controllers\Private\Marketing\Repository\UpdateRepositoryController;
use App\Http\Controllers\Private\Media\ListenerGallery\DestroyListenerGalleryController;
use App\Http\Controllers\Private\Media\ListenerGallery\ShowListenerGalleryController;
use App\Http\Controllers\Private\Media\ListenerGallery\StoreListenerGalleryController;
use App\Http\Controllers\Private\Media\ListenerGallery\UpdateListenerGalleryController;
use App\Http\Controllers\Private\Media\Poll\DeactivatePollController;
use App\Http\Controllers\Private\Media\Poll\ShowPollController;
use App\Http\Controllers\Private\Media\Poll\StorePollController;
use App\Http\Controllers\Private\Media\Poll\UpdatePollController;
use App\Http\Controllers\Private\Media\Poll\Vote\StoreVoteController;
use App\Http\Controllers\Private\Podcast\DeactivatePodcastController;
use App\Http\Controllers\Private\Podcast\ShowPodcastController;
use App\Http\Controllers\Private\Podcast\StorePodcastController;
use App\Http\Controllers\Private\Podcast\UpdatePodcastController;
use App\Http\Controllers\Private\Post\DeactivatePostController;
use App\Http\Controllers\Private\Post\ShowPostController;
use App\Http\Controllers\Private\Post\StorePostController;
use App\Http\Controllers\Private\Post\UpdatePostController;
use App\Http\Controllers\Private\Profile\User\UpdateProfileController;
use App\Http\Controllers\Private\Radio\ListenerMonth\StoreListenerMonthController;
use App\Http\Controllers\Private\Radio\Music\RefreshMusicRankingController;
use App\Http\Controllers\Private\Radio\Music\UpdateMusicController;
use App\Http\Controllers\Private\Radio\Program\DeactivateProgramController;
use App\Http\Controllers\Private\Radio\Program\ShowProgramController;
use App\Http\Controllers\Private\Radio\Program\StoreProgramController;
use App\Http\Controllers\Private\Radio\Program\UpdateProgramController;

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

    Route::middleware(['auth'])->group(function () {
        Route::post('logout', [LogoutController::class, '__invoke'])->name('logout');

        Route::prefix('dashboard')->controller(DashboardPageController::class)->group(function () {
            Route::get('', 'render')->name('panel.dashboard');
            Route::prefix('activity')->group(function () {
                Route::post('{activity:uuid}/confirm', [ConfirmActivityParticipantController::class, '__invoke']);
            });
            Route::prefix('task')->group(function () {
                Route::post('{task:uuid}/complete', [MarkTaskToReviewController::class, '__invoke']);
            });
            Route::prefix('post')->group(function () {
                Route::delete('{post:uuid}', [DeactivatePostController::class, '__invoke']);
            });
        });

        Route::prefix('post')->controller(PostPageController::class)->group(function () {
            Route::get('', 'render')->name('panel.post');
            Route::post('', [StorePostController::class, '__invoke']);
            Route::post('review', [StorePostController::class, '__invoke']);
            Route::patch('{post:uuid}', [UpdatePostController::class, '__invoke']);
            Route::get('{post:uuid}', [ShowPostController::class, '__invoke']);
            Route::delete('{post:uuid}', [DeactivatePostController::class, '__invoke']);
        });
        
        Route::prefix('locution')->controller(LocutionPageController::class)->group(function () {
            Route::prefix('locution')->group(function () {
                Route::post('start/{program:uuid}', [StartLocutionController::class, '__invoke']);
                Route::patch('finish', [FinishLocutionController::class, '__invoke']);
            });
            Route::prefix('songrequest')->group(function () {
                Route::patch('{songRequest:uuid}/played', [MarkSongRequestAsPlayedController::class, '__invoke']);
                Route::patch('{songRequest:uuid}/canceled', [MarkSongRequestAsCanceledController::class, '__invoke']);
                Route::patch('toggle', [ToggleSongRequestBoxStatusController::class, '__invoke']);
            });
            Route::get('', 'render')->name('panel.locucao');
        });

        Route::prefix('radio')->controller(RadioPageController::class)->group(function () {
            Route::prefix('program')->group(function () {
                Route::post('', [StoreProgramController::class, '__invoke']);
                Route::patch('{program:uuid}', [UpdateProgramController::class, '__invoke']);
                Route::get('{program:uuid}', [ShowProgramController::class, '__invoke']);
                Route::delete('{program:uuid}', [DeactivateProgramController::class, '__invoke']);
            });
            Route::prefix('music')->group(function () {
                Route::post('ranking', [RefreshMusicRankingController::class, '__invoke']);
                Route::patch('{music:uuid}', [UpdateMusicController::class, '__invoke']);
            });
            Route::prefix('listener-month')->group(function () {
                Route::post('', [StoreListenerMonthController::class, '__invoke']);
            });
            Route::get('', 'render')->name('panel.radio');
        });

        Route::prefix('podcast')->controller(PodcastPageController::class)->group(function () {
            Route::get('', 'render')->name('panel.podcast');
            Route::post('', [StorePodcastController::class, '__invoke']);
            Route::patch('{podcast:uuid}', [UpdatePodcastController::class, '__invoke']);
            Route::delete('{podcast:uuid}', [DeactivatePodcastController::class, '__invoke']);
            Route::get('{podcast:uuid}', [ShowPodcastController::class, '__invoke']);
        });

        Route::prefix('marketing')->controller(RepositoryPageController::class)->group(function () {
            Route::prefix('repository')->group(function () {
                Route::post('', [StoreRepositoryController::class, '__invoke']);
                Route::get('{repository:uuid}', [ShowRepositoryController::class, '__invoke']);
                Route::patch('{repository:uuid}', [UpdateRepositoryController::class, '__invoke']);
                Route::delete('{repository:uuid}', [DeactivateRepositoryController::class, '__invoke']);
            });
            Route::get('', 'render')->name('panel.marketing');
        });

        Route::prefix('media')->controller(MediaPageController::class)->group(function () {
            Route::prefix('event')->group(function () {
                Route::delete('{event:uuid}', 'deactivateEvent');
            });
            Route::prefix('listener-gallery')->group(function () {
                Route::post('', [StoreListenerGalleryController::class, '__invoke']);
                Route::get('{listenerGallery:uuid}', [ShowListenerGalleryController::class, '__invoke']);
                Route::patch('{listenerGallery:uuid}', [UpdateListenerGalleryController::class, '__invoke']);
                Route::delete('{listenerGallery:uuid}', [DestroyListenerGalleryController::class, '__invoke']);
            });
            Route::prefix('poll')->group(function () {
                Route::post('', [StorePollController::class, '__invoke']);
                Route::patch('{poll:uuid}', [UpdatePollController::class, '__invoke']);
                Route::delete('{poll:uuid}', [DeactivatePollController::class, '__invoke']);
                Route::get('{poll:uuid}', [ShowPollController::class, '__invoke']);
                Route::prefix('vote')->group(function () {
                    Route::post('{option:uuid}', [StoreVoteController::class, '__invoke']);
                });
            });
            Route::get('', 'render')->name('panel.medias');
        });

        Route::prefix('administration')->controller(AdministrationPageController::class)->group(function () {
            Route::prefix('user')->group(function () {
                Route::post('', [StoreUserController::class, '__invoke']);
                Route::get('{user:uuid}', [ShowUserController::class, '__invoke']);
                Route::delete('{user:uuid}', [DeactivateUserController::class, '__invoke']);
                Route::patch('{user:uuid}', [UpdateUserAccessController::class, '__invoke']);
                Route::prefix('role')->group(function () {
                    Route::patch('{user:uuid}', 'changeUserRoles');
                });
            });
            Route::prefix('role')->group(function(){
                Route::post('', [StoreRoleController::class, '__invoke']);
                Route::get('{role:uuid}', [ShowRoleController::class, '__invoke']);
                Route::patch('{role:uuid}', [UpdateRoleController::class, '__invoke']);
                Route::delete('{role:uuid}', [DestroyRoleController::class, '__invoke']);
            });
            Route::prefix('calendar')->group(function(){
                Route::post('', [StoreCalendarController::class, '__invoke']);
                Route::get('{calendar:uuid}', [ShowCalendarController::class, '__invoke']);
                Route::patch('{calendar:uuid}', [UpdateCalendarController::class, '__invoke']);
            });
            Route::prefix('activity')->group(function () {
                Route::post('', [StoreActivityController::class, '__invoke']);
                Route::get('{activity:uuid}', [ShowActivityController::class, '__invoke']);
                Route::patch('{activity:uuid}', [UpdateActivityController::class, '__invoke']);
                Route::delete('{activity:uuid}', 'removeActivity');
            });
            Route::prefix('task')->group(function () {
                Route::get('{task:uuid}', [ShowTaskController::class, '__invoke']);
                Route::post('', [StoreTaskController::class, '__invoke']);
                Route::patch('{task:uuid}', [UpdateTaskController::class, '__invoke']);
            });
            Route::get('', 'render')->name('panel.adms');
        });
        Route::prefix('logs')->controller(LogsPageController::class)->group(function () {
            Route::get('', 'render')->name('panel.logs');
        });
        Route::prefix('profile')->controller(ProfilePageController::class)->group(function () {
            Route::patch('{user:uuid}', [UpdateProfileController::class, '__invoke']);
            Route::get('{user:uuid}', 'render')->name('panel.profile');
        });
    });
});
