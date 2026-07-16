<?php

namespace App\Http\Controllers\Private\Radio\Music;

use App\Actions\Music\UpdateMusicAction;
use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Music\UpdateMusicRequest;
use App\Models\Music;

class UpdateMusicController extends Controller
{
    use HasFlashMessages;

    public function __invoke(UpdateMusicRequest $request, UpdateMusicAction $updateMusicAction, Music $music)
    {
        $updateMusicAction->execute(
            $music,
            $request->validated(),
            $request->file('image'),
            $request->file('image_ranking')
        );

        return $this->flashMessage('update');
    }
}
