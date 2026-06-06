<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StreamController;
use App\Http\Controllers\Api\AnimeController;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::prefix('anime')->group(function () {
    Route::controller(AnimeController::class)->group(function () {
        Route::get('music', 'getMusic');
    });
});

Route::prefix('stream')->group(function () {
    Route::controller(StreamController::class)->group(function () {
        Route::get('', 'redirectStream');
        Route::get('metadata', 'showMetadata');
    });
});
