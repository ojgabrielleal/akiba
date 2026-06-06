<?php

use Illuminate\Support\Facades\Route;

// Private controllers
use App\Http\Controllers\Private\LoginController;
use App\Http\Controllers\Private\AdministrationController;
use App\Http\Controllers\Private\LocutionController;
use App\Http\Controllers\Private\DashboardController;
use App\Http\Controllers\Private\PostController;
use App\Http\Controllers\Private\RadioController;
use App\Http\Controllers\Private\PodcastController;
use App\Http\Controllers\Private\RepositoryController;
use App\Http\Controllers\Private\MediaController;
use App\Http\Controllers\Private\LogsController;
use App\Http\Controllers\Private\ProfileController;

/*
|--------------------------------------------------------------------------
| Private routes
|--------------------------------------------------------------------------
*/
Route::prefix('panel')->middleware(['inertia'])->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('', 'render')->name('login');
        Route::post('auth', 'login');
    });

    Route::middleware(['auth'])->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
            Route::get('', 'render')->name('panel.dashboard');
            Route::prefix('activity')->group(function () {
                Route::post('{activity:uuid}/confirm', 'confirmActivityParticipant');
            });
            Route::prefix('task')->group(function () {
                Route::post('{task:uuid}/complete', 'markTaskToReview');
            });
            Route::prefix('post')->group(function () {
                Route::delete('{post:uuid}', 'deactivatePost');
            });
        });

        Route::prefix('post')->controller(PostController::class)->group(function () {
            Route::get('', 'render')->name('panel.post');
            Route::post('', 'createPost');
            Route::post('review', 'createReview');
            Route::patch('{post:uuid}', 'updatePost');
            Route::get('{post:uuid}', 'showPost');
            Route::delete('{post:uuid}', 'deactivatePost');
        });
        
        Route::prefix('locution')->controller(LocutionController::class)->group(function () {
            Route::prefix('locution')->group(function () {
                Route::post('start/{program:uuid}', 'startLocution');
                Route::patch('finish', 'finishLocution');
            });
            Route::prefix('songrequest')->group(function () {
                Route::patch('{songRequest:uuid}/played', 'markSongRequestAsPlayed');
                Route::patch('{songRequest:uuid}/canceled', 'markSongRequestAsCanceled');
                Route::patch('toggle', 'toggleSongRequestBoxStatus');
            });
            Route::get('', 'render')->name('panel.locucao');
        });

        Route::prefix('radio')->controller(RadioController::class)->group(function () {
            Route::prefix('program')->group(function () {
                Route::post('', 'createProgram');
                Route::patch('{program:uuid}', 'updateProgram');
                Route::get('{program:uuid}', 'showProgram');
                Route::delete('{program:uuid}', 'deactivateProgram');
            });
            Route::prefix('music-ranking')->group(function () {
                Route::post('', 'generateMusicRanking');
                Route::patch('{music:uuid}', 'updateMusicRanking');
            });
            Route::prefix('listener-month')->group(function () {
                Route::post('', 'createListenerMonth');
                Route::get('found', 'showListenerMonthFound');
            });
            Route::get('', 'render')->name('panel.radio');
        });

        Route::prefix('podcast')->controller(PodcastController::class)->group(function () {
            Route::get('', 'render')->name('panel.podcast');
            Route::post('', 'createPodcast');
            Route::patch('{podcast:uuid}', 'updatePodcast');
            Route::delete('{podcast:uuid}', 'deactivatePodcast');
            Route::get('{podcast:uuid}', 'showPodcast');
        });

        Route::prefix('marketing')->controller(RepositoryController::class)->group(function () {
            Route::prefix('repository')->group(function () {
                Route::post('', 'createRepository');
                Route::get('{repository:uuid}', 'showRepository');
                Route::patch('{repository:uuid}', 'updateRepository');
                Route::delete('{repository:uuid}', 'deactivateRepository');
            });
            Route::get('', 'render')->name('panel.marketing');
        });

        Route::prefix('media')->controller(MediaController::class)->group(function () {
            Route::prefix('event')->group(function () {
                Route::delete('{event:uuid}', 'deactivateEvent');
            });
            Route::prefix('poll')->group(function () {
                Route::post('', 'createPoll');
                Route::patch('{poll:uuid}', 'updatePoll');
                Route::delete('{poll:uuid}', 'deactivatePoll');
                Route::get('{poll:uuid}', 'showPoll');
                Route::prefix('vote')->group(function () {
                    Route::post('{pollOption:uuid}', 'createVote');
                });
            });
            Route::get('', 'render')->name('panel.medias');
        });

        Route::prefix('administration')->controller(AdministrationController::class)->group(function () {
            Route::prefix('user')->group(function () {
                Route::post('', 'createUser');
                Route::get('{user:uuid}', 'showUser');
                Route::delete('{user:uuid}', 'deactivateUser');
                Route::patch('{user:uuid}', 'updateUserAccess');
                Route::prefix('role')->group(function () {
                    Route::patch('{user:uuid}', 'changeUserRoles');
                });
            });
            Route::prefix('role')->group(function(){
                Route::post('', 'createRole');
                Route::get('{role:uuid}', 'showRole');
                Route::patch('{role:uuid}', 'updateRole');
                Route::delete('{role:uuid}', 'removeRole');
            });
            Route::prefix('calendar')->group(function(){
                Route::post('', 'createCalendar');
                Route::get('{calendar:uuid}', 'showCalendar');
                Route::patch('{calendar:uuid}', 'updateCalendar');
            });
            Route::prefix('activity')->group(function () {
                Route::post('', 'createActivity');
                Route::get('{activity:uuid}', 'showActivity');
                Route::patch('{activity:uuid}', 'updateActivity');
                Route::delete('{activity:uuid}', 'removeActivity');
            });
            Route::prefix('task')->group(function () {
                Route::get('{task:uuid}', 'showTask');
                Route::post('', 'createTask');
                Route::patch('{task:uuid}', 'updateTask');
            });
            Route::get('', 'render')->name('panel.adms');
        });
        Route::prefix('logs')->controller(LogsController::class)->group(function () {
            Route::get('', 'render')->name('panel.logs');
        });
        Route::prefix('profile')->controller(ProfileController::class)->group(function () {
            Route::patch('{user:uuid}', 'updateProfile');
            Route::get('{user:uuid}', 'render')->name('panel.profile');
        });
    });
});
