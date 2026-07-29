<?php

namespace App\Actions\Post;

use App\Models\OAuthAccount;
use App\Models\Post;
use App\Models\PostReaction;

use Illuminate\Support\Facades\DB;

class StorePostReactionAction
{
    public function execute(Post $post, OAuthAccount $oauthAccount, string $name): PostReaction
    {
        return DB::transaction(fn () => $post->reactions()->updateOrCreate(
            ['oauth_account_id' => $oauthAccount->id],
            ['name' => $name],
        ));
    }
}
