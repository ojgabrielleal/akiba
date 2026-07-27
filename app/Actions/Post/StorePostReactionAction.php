<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\PostReaction;

class StorePostReactionAction
{
    public function execute(Post $post, string $name): PostReaction
    {
        return $post->reactions()->create([
            'name' => $name,
        ]);
    }
}
