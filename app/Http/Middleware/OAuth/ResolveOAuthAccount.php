<?php

namespace App\Http\Middleware\OAuth;

use App\Models\OAuthAccount;
use Illuminate\Database\Eloquent\Model;

use Closure;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Laravel\Sanctum\PersonalAccessToken;

use Symfony\Component\HttpFoundation\Response;

class ResolveOAuthAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        Inertia::share('oauth', self::resolve($request));

        return $next($request);
    }

    public static function resolve(Request $request): array
    {
        $oauthToken = $request->cookie('akiba_oauth_token');

        $authenticatedUser = $request->user();
        $user = $authenticatedUser?->loadMissing('topAnimes');
        $oauthAccount = null;

        if ($oauthToken) {
            $accessToken = PersonalAccessToken::findToken($oauthToken);
            $tokenable = $accessToken?->tokenable;

            if (
                $tokenable instanceof OAuthAccount
                && $accessToken->can('public')
                && (! $accessToken->expires_at || $accessToken->expires_at->isFuture())
            ) {
                $oauthAccount = $tokenable;
                $accessToken->forceFill(['last_used_at' => now()])->save();
                $request->attributes->set('oauth_account', $oauthAccount);
            }
        }

        return [
            'type' => $user ? 'member' : ($oauthAccount ? 'oauth' : null),
            'authenticated' => $user !== null || $oauthAccount instanceof OAuthAccount,
            'is_member' => $user !== null,
            'member_session_authenticated' => $authenticatedUser !== null,
            'is_oauth' => $user === null && $oauthAccount instanceof OAuthAccount,
            'profile_completed' => $user !== null || $oauthAccount?->profile_completed_at !== null,
            'can_view_profile' => $user?->hasPermission('user.view.own') ?? true,
            'can_update_profile' => $user?->hasPermission('user.update.own') ?? true,
            'profile' => $user ? [
                'uuid' => $user->uuid,
                'provider' => 'internal',
                'username' => $user->name,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'birth_date' => $user->birth_date?->format('Y-m-d'),
                'address' => collect([$user->city, $user->state])->filter()->join(' - '),
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
                'bio' => $user->bibliography,
                'top_anime' => self::profileTopAnime($user),
                'top_animes' => self::profileTopAnimes($user),
                'badges' => self::profileBadges($user),
            ] : ($oauthAccount ? [
                'uuid' => $oauthAccount->uuid,
                'provider' => $oauthAccount->provider,
                'username' => $oauthAccount->username,
                'nickname' => $oauthAccount->nickname,
                'avatar' => $oauthAccount->avatar,
                'birth_date' => $oauthAccount->birth_date?->format('Y-m-d'),
                'address' => $oauthAccount->address,
                'top_anime' => self::normalizeTopAnime($oauthAccount->top_anime ?? []),
                'badges' => self::profileBadges($oauthAccount),
            ] : null),
        ];
    }

    private static function profileTopAnimes(Model $member): array
    {
        if (! method_exists($member, 'topAnimes')) {
            return [];
        }

        return $member->topAnimes
            ->map(fn ($topAnime) => self::normalizeTopAnime([
                'position' => $topAnime->position,
                'anime_theme_list_id' => $topAnime->anime_theme_list_id,
                'slug' => $topAnime->slug,
                'name' => $topAnime->name,
                'image' => $topAnime->image,
                'metadata' => $topAnime->metadata,
            ]))
            ->filter()
            ->values()
            ->all();
    }

    private static function profileTopAnime(Model $member): ?array
    {
        if (! method_exists($member, 'topAnimes')) {
            return null;
        }

        $topAnime = $member->topAnimes->firstWhere('position', 1);

        if (! $topAnime) {
            return null;
        }

        return self::normalizeTopAnime([
            'anime_theme_list_id' => $topAnime->anime_theme_list_id,
            'slug' => $topAnime->slug,
            'name' => $topAnime->name,
            'image' => $topAnime->image,
            'metadata' => $topAnime->metadata,
        ]);
    }

    private static function normalizeTopAnime(?array $topAnime): ?array
    {
        if (blank($topAnime['name'] ?? null)) {
            return null;
        }

        return [
            'position' => $topAnime['position'] ?? null,
            'anime_theme_list_id' => $topAnime['anime_theme_list_id'] ?? null,
            'slug' => $topAnime['slug'] ?? null,
            'name' => $topAnime['name'] ?? null,
            'image' => $topAnime['image'] ?? null,
            'metadata' => $topAnime['metadata'] ?? null,
        ];
    }

    private static function profileBadges(Model $member): array
    {
        return $member->badgeAssignments()
            ->active()
            ->with('badge')
            ->latest('acquired_at')
            ->get()
            ->map(fn ($assignment) => [
                'uuid' => $assignment->badge?->uuid,
                'name' => $assignment->badge?->name,
                'code' => $assignment->badge?->code,
                'image' => $assignment->badge?->image,
                'type' => $assignment->badge?->type,
                'acquired_at' => $assignment->acquired_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ])
            ->filter(fn (array $badge) => filled($badge['uuid']))
            ->values()
            ->all();
    }

}
