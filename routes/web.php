<?php 

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require __DIR__.'/web/public.php';
require __DIR__.'/web/private.php';
require __DIR__.'/web/provisory.php';

Route::fallback(function () {
    if (request()->is('panel') || request()->is('panel/*')) {
        abort(404);
    }

    return Inertia::render('public/NotFound')
        ->toResponse(request())
        ->setStatusCode(404);
})->middleware(['oauth.resolve', 'inertia']);
