<?php

namespace Database\Factories;

use App\Models\OAuthAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SongRequest>
 */
class SongRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'was_reproduced' => false,
            'was_canceled' => false,
            'requester_type' => OAuthAccount::class,
            'requester_id' => OAuthAccount::factory(),
            'message' => fake()->sentence(),
        ];
    }
}
