<?php

namespace App\Support;

use App\Models\OAuthAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuthenticatedMember
{
    public static function fromRequest(Request $request): ?Model
    {
        if ($request->user()) {
            return $request->user();
        }

        $memberUser = $request->attributes->get('member_user');
        if ($memberUser instanceof User) {
            return $memberUser;
        }

        $oauthAccount = $request->attributes->get('oauth_account');
        if ($oauthAccount instanceof OAuthAccount) {
            return $oauthAccount;
        }
    }
}
