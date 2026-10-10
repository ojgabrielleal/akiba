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
        $requester = AuthenticatedMember::fromRequest($request);

        abort_unless($requester, 403);

        $service->store($request->validated(), $requester);

        return back(303);
    }
}
