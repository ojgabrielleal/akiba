<?php

namespace App\Http\Controllers\Public;

use App\Actions\SongRequest\StoreSongRequestAction;

use App\Http\Controllers\Controller;

use App\Http\Requests\SongRequest\StoreSongRequestRequest;

use Illuminate\Http\RedirectResponse;

class SongRequestController extends Controller
{
    public function store(StoreSongRequestRequest $request, StoreSongRequestAction $action): RedirectResponse
    {
        $action->execute(
            $request->validated(),
            $request->attributes->get('oauth_account'),
        );

        return back(303);
    }
}
