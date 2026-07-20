<?php

namespace Database\Factories;

use App\Models\OAuthAccount;
use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListenerMonth>
 */
class ListenerMonthFactory extends Factory
{
    use HasFakeImages;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'oauth_account_id' => OAuthAccount::factory(),
            'favorite_program' => [
                'name' => fake()->name(),
                'image' => $this->fakeImageUrl(),
            ],
            'favorite_music' => [
                'name' => fake()->name(),
                'artist' => fake()->name(),
                'production' => fake()->name(),
                'image' => $this->fakeImageUrl(),
            ],
            'requests_total' => fake()->randomNumber(),
        ];
    }
}
