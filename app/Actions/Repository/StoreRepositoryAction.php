<?php

namespace App\Actions\Repository;

use App\Models\Repository;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StoreRepositoryAction
{
    public function __construct(private ImageProcessService $image)
    {
    }

    public function execute(array $data, UploadedFile $image): Repository
    {
        return DB::transaction(fn () => Repository::create([
            'name' => $data['name'],
            'url' => $data['url'],
            'image' => $this->image->store('repository', $image),
            'type' => $data['type'],
        ]));
    }
}
