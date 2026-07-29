<?php

namespace App\Actions\Post;

use App\Models\OAuthAccount;
use App\Models\Post;
use App\Models\PostComment;

use Illuminate\Support\Facades\DB;

class StorePostCommentAction
{
    public function execute(Post $post, OAuthAccount $oauthAccount, array $data): PostComment
    {
        return DB::transaction(fn () => $post->comments()->create([
            'oauth_account_id' => $oauthAccount->id,
            'comment' => $data['comment'],
        ]));
    }
}
