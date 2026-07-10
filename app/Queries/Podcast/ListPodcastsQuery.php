<?php

namespace App\Queries\Podcast;

use App\Http\Resources\PodcastResource;
use App\Models\Podcast;
use App\Models\User;

class ListPodcastsQuery
{
    public function handle(User $user)
    {
        if($user->cannot('viewAny', Podcast::class)){
            abort(403);
        }

        return PodcastResource::collection(
            Podcast::active()
                ->latest()
                ->with('author', 'views')
                ->paginate(10)
        );
    }
}
