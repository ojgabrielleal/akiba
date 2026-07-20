<?php

use Illuminate\Support\Facades\Route;

// Provisory controllers
use App\Http\Controllers\Provisory\HomeController;
use App\Http\Controllers\Public\SongRequestController;

/*
|--------------------------------------------------------------------------
| Provisory routes
|--------------------------------------------------------------------------
*/
Route::prefix("")->middleware(['oauth.resolve', 'inertia'])->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get('', 'render')->name('home');
    });

    Route::post('song-request', [SongRequestController::class, 'store'])
        ->middleware('oauth');
});
