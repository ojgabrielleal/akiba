<?php

use App\Http\Controllers\Private\AdministrationController;
use App\Http\Controllers\Private\DashboardController;
use App\Http\Controllers\Private\TrashController;
use App\Http\Controllers\Private\LocutionController;
use App\Http\Controllers\Private\LoginController;
use App\Http\Controllers\Private\MediaController;
use App\Http\Controllers\Private\PodcastController;
use App\Http\Controllers\Private\PostController;
use App\Http\Controllers\Private\ProfileController;
use App\Http\Controllers\Private\PushNotificationController;
use App\Http\Controllers\Private\RadioController;
use App\Http\Controllers\Private\ReportsController;
use App\Http\Controllers\Private\RepositoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Private routes
|--------------------------------------------------------------------------
*/
Route::prefix('panel')->middleware(['inertia'])->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('', 'render')->name('login');
        Route::post('auth', 'loginUser');
    });

    Route::middleware(['auth', 'authenticated.user'])->group(function () {
        Route::post('logout', [LoginController::class, 'logoutUser'])->name('logout');
        Route::post('push-notification', [PushNotificationController::class, 'storePushNotification']);
        Route::delete('push-notification', [PushNotificationController::class, 'destroyPushNotification']);

        Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
            Route::get('', 'render')->name('panel.dashboard');
            Route::prefix('activity')->group(function () {
                Route::post('{activity:uuid}/confirm', 'confirmActivityParticipant');
            });
            Route::prefix('task')->group(function () {
                Route::post('{task:uuid}/review', 'markTaskToReview');
            });
        });

        Route::prefix('post')->controller(PostController::class)->group(function () {
            Route::get('', 'render')->name('panel.post');
            Route::post('', 'storePost');
            Route::patch('{post:uuid}', 'updatePost');
            Route::get('{post:uuid}', 'showPost');
            Route::patch('{post:uuid}/deactivate', 'deactivatePost');
        });

        Route::prefix('locution')->controller(LocutionController::class)->group(function () {
            Route::post('start/{program:uuid}', 'startLocution');
            Route::patch('finish', 'finishLocution');
            Route::prefix('song-request')->group(function () {
                Route::patch('{songRequest:uuid}/played', 'markSongRequestAsPlayed');
                Route::patch('{songRequest:uuid}/canceled', 'markSongRequestAsCanceled');
                Route::patch('box/toggle', 'toggleSongRequestBoxStatus');
            });
            Route::get('', 'render')->name('panel.locution');
        });

        Route::prefix('radio')->controller(RadioController::class)->group(function () {
            Route::prefix('program')->group(function () {
                Route::post('', 'storeProgram');
                Route::patch('{program:uuid}', 'updateProgram');
                Route::patch('{program:uuid}/deactivate', 'deactivateProgram');
            });
            Route::prefix('music')->group(function () {
                Route::post('ranking/refresh', 'refreshMusicRanking');
                Route::patch('{music:uuid}', 'updateMusic');
            });
            Route::prefix('listener-month')->group(function () {
                Route::post('', 'storeListenerMonth');
            });
            Route::get('', 'render')->name('panel.radio');
        });

        Route::prefix('podcast')->controller(PodcastController::class)->group(function () {
            Route::get('', 'render')->name('panel.podcast');
            Route::post('', 'storePodcast');
            Route::patch('{podcast:uuid}', 'updatePodcast');
            Route::patch('{podcast:uuid}/deactivate', 'deactivatePodcast');
            Route::get('{podcast:uuid}', 'showPodcast');
        });

        Route::prefix('marketing')->controller(RepositoryController::class)->group(function () {
            Route::prefix('repository')->group(function () {
                Route::post('', 'storeRepository');
                Route::get('{repository:uuid}', 'showRepository');
                Route::patch('{repository:uuid}', 'updateRepository');
                Route::patch('{repository:uuid}/deactivate', 'deactivateRepository');
            });
            Route::get('', 'render')->name('panel.marketing');
        });

        Route::prefix('media')->middleware('can:media.module.view')->controller(MediaController::class)->group(function () {
            Route::prefix('listener-gallery')->group(function () {
                Route::post('', 'storeListenerGallery');
                Route::get('{listenerGallery:uuid}', 'showListenerGallery');
                Route::patch('{listenerGallery:uuid}', 'updateListenerGallery');
                Route::delete('{listenerGallery:uuid}', 'destroyListenerGallery');
            });
            Route::prefix('poll')->group(function () {
                Route::post('', 'storePoll');
                Route::patch('{poll:uuid}', 'updatePoll');
                Route::patch('{poll:uuid}/deactivate', 'deactivatePoll');
                Route::post('option/{option:uuid}/vote', 'votePollOption');
            });
            Route::get('', 'render')->name('panel.media');
        });

        Route::prefix('administration')->controller(AdministrationController::class)->group(function () {
            Route::prefix('user')->group(function () {
                Route::post('', 'storeUser');
                Route::get('{user:uuid}', 'showUser');
                Route::patch('{user:uuid}/deactivate', 'deactivateUser');
                Route::patch('{user:uuid}', 'updateUserAccess');
            });
            Route::prefix('role')->group(function () {
                Route::post('', 'storeRole');
                Route::get('{role:uuid}', 'showRole');
                Route::patch('{role:uuid}', 'updateRole');
                Route::delete('{role:uuid}', 'destroyRole');
            });
            Route::prefix('calendar')->group(function () {
                Route::post('', 'storeCalendar');
                Route::get('{calendar:uuid}', 'showCalendar');
                Route::patch('{calendar:uuid}', 'updateCalendar');
            });
            Route::prefix('activity')->group(function () {
                Route::post('', 'storeActivity');
                Route::get('{activity:uuid}', 'showActivity');
                Route::patch('{activity:uuid}', 'updateActivity');
            });
            Route::prefix('task')->group(function () {
                Route::get('{task:uuid}', 'showTask');
                Route::post('', 'storeTask');
                Route::patch('{task:uuid}', 'updateTask');
                Route::patch('{task:uuid}/complete', 'completeTask');
                Route::patch('{task:uuid}/deactivate', 'deactivateTask');
            });
            Route::prefix('form-submission')->group(function () {
                Route::patch('{formSubmission:uuid}/approve', 'approveFormSubmission')
                    ->middleware('can:form.submission.review');
                Route::patch('{formSubmission:uuid}/reject', 'rejectFormSubmission')
                    ->middleware('can:form.submission.review');
                Route::post('{formSubmission:uuid}/comment', 'commentFormSubmission')
                    ->middleware('can:form.submission.review');
                Route::delete('{formSubmission:uuid}', 'destroyFormSubmission')
                    ->middleware('can:form.submission.review');
            });
            Route::get('', 'render')->name('panel.administration');
        });
        Route::prefix('reports')->middleware('can:report.module.view')->controller(ReportsController::class)->group(function () {
            Route::get('', 'render')->name('panel.reports');
        });
        Route::prefix('trash')->middleware('can:trash.module.view')->group(function () {
            Route::get('', [TrashController::class, 'render'])->name('panel.trash');
            Route::patch('{type}/{uuid}/reactivate', [TrashController::class, 'reactivateTrashItem'])
                ->middleware('can:trash.restore');
            Route::delete('{type}/{uuid}', [TrashController::class, 'destroyTrashItem'])
                ->middleware('can:trash.delete');
        });
        Route::prefix('profile')->controller(ProfileController::class)->group(function () {
            Route::patch('{user:uuid}', 'updateProfile');
            Route::get('{user:uuid}', 'render')->name('panel.profile');
        });
    });
});
