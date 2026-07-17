<?php

namespace App\Http\Controllers\Private;

use App\Actions\Music\UpdateMusicAction;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;

use App\Http\Requests\Music\UpdateMusicRequest;

use App\Models\Music;

class MusicController extends Controller
{
    use HasFlashMessages;

    public function __construct(
        private UpdateMusicAction $updateMusicAction,
    ) {}

    public function update(UpdateMusicRequest $request, Music $music)
    {
        $this->updateMusicAction->execute(
            $music,
            $request->validated(),
            $request->file('image'),
            $request->file('image_ranking')
        );

        return $this->flashMessage('update');
    }
}
