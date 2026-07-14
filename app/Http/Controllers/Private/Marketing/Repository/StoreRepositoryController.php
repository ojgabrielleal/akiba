<?php

namespace App\Http\Controllers\Private\Marketing\Repository;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Marketing\CreateRepositoryRequest;
use App\Models\Repository;
use App\Services\Process\ImageProcessService;

class StoreRepositoryController extends Controller
{
    use HasFlashMessages;

    public function __invoke(CreateRepositoryRequest $request, ImageProcessService $image)
    {
        Repository::create([
            'name' => $request->input('name'),
            'url' => $request->input('url'),
            'image' => $image->store('repository', $request->file('image')),
            'type' => $request->input('type'),
        ]);

        return $this->flashMessage('save');
    }
}
