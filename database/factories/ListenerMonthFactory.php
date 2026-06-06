<?php

namespace Database\Factories;

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
            'name' => fake()->name(),
            'avatar' => $this->fakeImageUrl(),
            'address' => fake()->address(),
            'favorite_program' => [
                'name' => fake()->name(),
            ],
            'favorite_anime' => [
                'name' => fake()->name(),
            ],
            'requests_total' => fake()->randomNumber(),
        ];
    }
}
