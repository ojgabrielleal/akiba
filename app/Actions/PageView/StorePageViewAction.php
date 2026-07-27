<?php

namespace App\Actions\PageView;

use App\Models\PageView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class StorePageViewAction
{
    public function execute(Model $viewable, Request $request): PageView
    {
        return $viewable->views()->create([
            'ip_address' => $request->ip(),
            'page_name' => $viewable->title ?? null,
            'page_url' => $request->fullUrl(),
        ]);
    }
}
