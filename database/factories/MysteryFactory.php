<?php

namespace Database\Factories;

use App\Models\Mystery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mystery>
 */
class MysteryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'content' => fake()->paragraph(2),
            'status' => fake()->randomElement([
                Mystery::STATUS_DRAFT,
                Mystery::STATUS_ACTIVE,
                Mystery::STATUS_INACTIVE,
            ]),
            'solution' => fake()->sentence(4),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => Mystery::STATUS_ACTIVE,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => Mystery::STATUS_DRAFT,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => Mystery::STATUS_INACTIVE,
        ]);
    }
}
