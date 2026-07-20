<?php

namespace App\Http\Resources\Poll;

use App\Models\OAuthAccount;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'question' => $this->question,
            'expires_at' => $this->expires_at?->setTimezone('America/Sao_Paulo')->format('Y-m-d\TH:i'),
            'options' => PollOptionResource::collection($this->options),
            'total_votes' => $this->votes_count,
            'has_voted' => $this->hasVoteFrom($request),
            'is_valid' => $this->isValid(),
        ];
    }

    private function isValid(): bool
    {
        return $this->is_active && (
            $this->expires_at === null || $this->expires_at->isFuture()
        );
    }

    private function hasVoteFrom(Request $request): bool
    {
        $user = $request->user();
        $oauthAccount = $this->oauthAccount($request);

        if (!$user && !$oauthAccount) return false;

        return $this->votes->contains(
            fn ($vote) => ($user && $vote->user_id === $user->id) || ($oauthAccount && $vote->oauth_id === $oauthAccount->id)
        );
    }

    private function oauthAccount(Request $request): ?OAuthAccount
    {
        $oauthAccount = $request->attributes->get('oauth_account');
        if ($oauthAccount instanceof OAuthAccount) return $oauthAccount;

        $oauthToken = $request->cookie('akiba_oauth_token');
        if (!$oauthToken) return null;

        return OAuthAccount::where('account_token_hash', hash('sha256', $oauthToken))->first();
    }
}
