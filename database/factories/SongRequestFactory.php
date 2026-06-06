<?php

namespace Database\Factories;

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
            'ip_address' => fake()->ipv4(),
            'name' => fake()->userName(),
            'address' => fake()->address(),
            'message' => fake()->sentence(),
        ];
    }
}
