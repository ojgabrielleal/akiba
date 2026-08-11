<?php

namespace Tests\Feature\Public;

use App\Models\OAuthAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Mockery;
use Tests\TestCase;

class OAuthAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_redirect_uses_socialite_provider(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect()->away('https://discord.com/oauth2/authorize'));

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')
            ->once()
            ->with('discord')
            ->andReturn($provider);

        $this->app->instance(SocialiteFactory::class, $socialite);

        $this
            ->get('/oauth/discord/redirect')
            ->assertRedirect('https://discord.com/oauth2/authorize');
    }

    public function test_oauth_redirect_rejects_unsupported_provider(): void
    {
        $this
            ->get('/oauth/github/redirect')
            ->assertNotFound();
    }

    public function test_oauth_callback_stores_provider_account(): void
    {
        $providerUser = Mockery::mock(ProviderUser::class);
        $providerUser->shouldReceive('getId')->andReturn('discord-123');
        $providerUser->shouldReceive('getNickname')->andReturn('akiba_user');
        $providerUser->shouldReceive('getName')->andReturn('Akiba User');
        $providerUser->shouldReceive('getAvatar')->andReturn('https://cdn.example/avatar.webp');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($providerUser);

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')
            ->once()
            ->with('discord')
            ->andReturn($provider);

        $this->app->instance(SocialiteFactory::class, $socialite);

        $this
            ->get('/oauth/discord/callback')
            ->assertRedirect(route('home'))
            ->assertCookie('akiba_oauth_token');

        $this->assertDatabaseHas('oauth_accounts', [
            'provider' => 'discord',
            'provider_user_id' => 'discord-123',
            'username' => 'akiba_user',
            'nickname' => 'Akiba User',
            'avatar' => 'https://cdn.example/avatar.webp',
        ]);

        $this->assertNotNull(OAuthAccount::first()->account_token_hash);
    }
}
