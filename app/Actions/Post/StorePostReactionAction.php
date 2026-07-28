<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\PostReaction;

use Illuminate\Support\Facades\DB;

class StorePostReactionAction
{
    public function execute(Post $post, string $name): PostReaction
    {
        return DB::transaction(fn () => $post->reactions()->create([
            'name' => $name,
        ]));
    }
}
