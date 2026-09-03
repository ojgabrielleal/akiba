<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrashService
{
    public function __construct(
        private CacheService $cache,
    ) {}

    public function destroy(Model $item): void
    {
        DB::transaction(fn () => $item->delete());

        $this->cache->invalidateTrash();
    }

    public function reactivate(Model $item): Model
    {
        $item = DB::transaction(function () use ($item) {
            $item->update(['is_active' => true]);

            return $item->refresh();
        });

        $this->cache->invalidateTrash();

        return $item;
    }
}
