<?php

namespace App\Actions\InactiveItem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReactivateInactiveItemAction
{
    public function execute(Model $item): Model
    {
        return DB::transaction(function () use ($item) {
            $item->update(['is_active' => true]);

            return $item->refresh();
        });
    }
}
