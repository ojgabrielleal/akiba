<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OAuthAccount>
 */
class OAuthAccountFactory extends Factory
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
            'provider' => 'discord',
            'provider_user_id' => $providerUserId,
            'username' => fake()->userName(),
            'nickname' => fake()->name(),
            'avatar' => fake()->imageUrl(),
            'birth_date' => fake()->date(),
            'address' => fake()->address(),
            'profile_completed_at' => now(),
        ];
    }
}
