<?php

namespace App\Actions\Post;

use App\Models\OAuthAccount;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class TogglePostLikeAction
{
    public function execute(Post $post, ?OAuthAccount $oauthAccount, string $visitorToken): bool
    {
        return DB::transaction(function () use ($post, $oauthAccount, $visitorToken) {
            $query = $post->likes();

            if ($oauthAccount) {
                $query->where('oauth_account_id', $oauthAccount->id);
            } else {
                $query->where('visitor_token', $visitorToken);
            }

            $like = $query->first();

            if ($like) {
                $like->delete();

                return false;
            }

            $post->likes()->create([
                'oauth_account_id' => $oauthAccount?->id,
                'visitor_token' => $oauthAccount ? null : $visitorToken,
            ]);

            return true;
        });
    }
}
