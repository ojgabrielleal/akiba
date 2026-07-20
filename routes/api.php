<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StreamController;
use App\Http\Controllers\Api\External\AnimeThemesController;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::prefix('anime-themes')->group(function () {
    Route::controller(AnimeThemesController::class)->group(function () {
        Route::get('search', 'search');
    });
});

Route::prefix('stream')->group(function () {
    Route::controller(StreamController::class)->group(function () {
        Route::redirect('', config('services.stream.url'));
        Route::get('metadata', 'showMetadata');
    });
});
