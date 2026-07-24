<?php

namespace Database\Factories;

use App\Models\OAuthAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListenerMonth>
 */
class ListenerMonthFactory extends Factory
{
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
                'image' => '/img/placeholders/avatar.webp',
            ],
            'favorite_music' => [
                'name' => fake()->name(),
                'artist' => fake()->name(),
                'production' => fake()->name(),
                'image' => '/img/placeholders/avatar.webp',
            ],
            'requests_total' => fake()->randomNumber(),
        ];
    }
}
