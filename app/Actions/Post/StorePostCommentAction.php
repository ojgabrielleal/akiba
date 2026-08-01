<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\PostComment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StorePostCommentAction
{
    public function execute(Post $post, Model $author, array $data): PostComment
    {
        return DB::transaction(function () use ($post, $author, $data) {
            $comment = $post->comments()->make([
                'comment' => $data['comment'],
            ]);

            $comment->author()->associate($author);
            $comment->save();

            return $comment;
        });
    }
}
