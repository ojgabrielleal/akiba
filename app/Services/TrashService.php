<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\EnigmaGame;

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
            $item instanceof EnigmaGame
                ? $item->update(['status' => EnigmaGame::STATUS_DRAFT])
                : $item->update(['is_active' => true]);

            return $item->refresh();
        });

        return $item;
    }
}
