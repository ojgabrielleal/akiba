<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\PostReaction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StorePostReactionAction
{
    public function execute(Post $post, Model $reactor, string $name): PostReaction
    {
        return DB::transaction(fn () => $post->reactions()->updateOrCreate(
            [
                'reactor_type' => $reactor->getMorphClass(),
                'reactor_id' => $reactor->getKey(),
            ],
            ['name' => $name],
        ));
    }
}
