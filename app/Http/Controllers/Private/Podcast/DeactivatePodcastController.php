<?php

namespace App\Http\Controllers\Private\Podcast;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Models\Podcast;

class DeactivatePodcastController extends Controller
{
    use HasFlashMessages;

    public function __invoke(Podcast $podcast)
    {
        $this->authorize('delete', $podcast);

        $podcast->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }
}
