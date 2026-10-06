<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrashService
{
    public function __construct(
    ) {}

    public function destroy(Model $item): void
    {
        DB::transaction(fn () => $item->delete());
    }

    public function reactivate(Model $item): Model
    {
        $item = DB::transaction(function () use ($item) {
            $item->update(['is_active' => true]);

            return $item->refresh();
        });

        return $item;
    }
}
