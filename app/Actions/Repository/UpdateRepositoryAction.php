<?php

namespace App\Actions\Repository;

use App\Models\Repository;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateRepositoryAction
{
    public function __construct(private ImageProcessService $image)
    {
    }

    public function execute(Repository $repository, array $data, ?UploadedFile $image = null): Repository
    {
        return DB::transaction(function () use ($repository, $data, $image) {
            $repository->fill([
                'name' => $data['name'] ?? $repository->name,
                'url' => $data['url'] ?? $repository->url,
                'image' => $this->image->store('repository', $image, $repository->image),
                'type' => $data['type'] ?? $repository->type,
            ]);

            if ($repository->isDirty()) {
                $repository->save();
            }

            return $repository;
        });
    }
}
