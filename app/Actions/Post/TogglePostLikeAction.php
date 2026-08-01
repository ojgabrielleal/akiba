<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TogglePostLikeAction
{
    public function execute(Post $post, ?Model $liker, string $visitorToken): bool
    {
        return DB::transaction(function () use ($post, $liker, $visitorToken) {
            $query = $post->likes();

            if ($liker) {
                $query
                    ->where('liker_type', $liker->getMorphClass())
                    ->where('liker_id', $liker->getKey());
            } else {
                $query->where('visitor_token', $visitorToken);
            }

            $like = $query->first();

            if ($like) {
                $like->delete();

                return false;
            }

            $like = $post->likes()->make([
                'visitor_token' => $liker ? null : $visitorToken,
            ]);

            if ($liker) {
                $like->liker()->associate($liker);
            }

            $like->save();

            return true;
        });
    }
}
