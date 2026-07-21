<?php

namespace App\Actions\OAuthAccount;

use App\Models\OAuthAccount;

use Illuminate\Support\Facades\DB;

class CompleteOAuthAccountProfileAction
{
    public function execute(OAuthAccount $oauthAccount, array $data): OAuthAccount
    {
        return DB::transaction(function () use ($oauthAccount, $data) {
            $oauthAccount->update([
                'nickname' => $data['nickname'],
                'birth_date' => $data['birth_date'],
                'address' => $data['address'],
                'bio' => $data['bio'] ?? null,
                'profile_completed_at' => now(),
            ]);

            return $oauthAccount;
        });
    }
}
