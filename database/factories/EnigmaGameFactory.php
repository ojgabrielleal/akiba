<?php

namespace Database\Factories;

use App\Models\EnigmaGame;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnigmaGame>
 */
class EnigmaGameFactory extends Factory
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
                EnigmaGame::STATUS_DRAFT,
                EnigmaGame::STATUS_ACTIVE,
                EnigmaGame::STATUS_ENDED,
                EnigmaGame::STATUS_INACTIVE,
            ]),
            'solution' => fake()->sentence(4),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => EnigmaGame::STATUS_ACTIVE,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => EnigmaGame::STATUS_DRAFT,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => EnigmaGame::STATUS_INACTIVE,
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn () => [
            'status' => EnigmaGame::STATUS_ENDED,
        ]);
    }
}
