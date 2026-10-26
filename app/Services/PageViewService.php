<?php

namespace App\Services;

use App\Models\PageView;
use App\Models\Post;
use App\Models\User;
use App\Support\AuthenticatedMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageViewService
{
    public function store(Model $viewable, Request $request): ?PageView
    {
        $actor = AuthenticatedMember::fromRequest($request);

        if ($viewable instanceof Post
            && $actor instanceof User
            && (int) $viewable->user_id === (int) $actor->getKey()) {
            return null;
        }

        return DB::transaction(fn () => $viewable->views()->create([
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'ip_address' => $request->ip(),
            'page_name' => $viewable->title ?? null,
            'page_url' => $request->fullUrl(),
        ]));
    }
}
