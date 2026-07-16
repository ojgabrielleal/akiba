<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class DeactivatePostAction
{
    public function execute(Post $post): Post
    {
        return DB::transaction(function () use ($post) {
            $post->update(['is_active' => false]);
            return $post;
        });
    }
}
