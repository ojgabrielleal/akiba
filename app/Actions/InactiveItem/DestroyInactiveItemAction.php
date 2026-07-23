<?php

namespace App\Actions\InactiveItem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DestroyInactiveItemAction
{
    public function execute(Model $item): void
    {
        DB::transaction(fn () => $item->delete());
    }
}
