<?php

namespace App\Actions\Repository;

use App\Models\Repository;

use Illuminate\Support\Facades\DB;

class DeactivateRepositoryAction
{
    public function execute(Repository $repository): Repository
    {
        return DB::transaction(function () use ($repository) {
            $repository->update(['is_active' => false]);

            return $repository;
        });
    }
}
