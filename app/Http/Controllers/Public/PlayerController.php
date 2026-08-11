<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\SongRequest\StoreSongRequestRequest;
use App\Services\SongRequestService;
use App\Support\AuthenticatedMember;
use Illuminate\Http\RedirectResponse;

class PlayerController extends Controller
{
    public function storeSongRequest(StoreSongRequestRequest $request, SongRequestService $service): RedirectResponse
    {
        $service->store($request->validated(), AuthenticatedMember::fromRequest($request));

        return back(303);
    }
}
