<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OAuth>
 */
class OAuthFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providerUserId = fake()->unique()->numerify('##################');

        return [
            'provider' => [
                'name' => 'discord',
                'user_id' => $providerUserId,
                'username' => fake()->userName(),
                'global_name' => fake()->name(),
                'avatar' => Str::random(32),
            ],
            'account_token_hash' => hash('sha256', Str::random(64)),
        ];
    }
}
