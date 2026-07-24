<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlaylistBattle>
 */
class PlaylistBattleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day' => fake()->randomElement([0, 1, 2, 3, 4, 5, 6]),
            'image' => '/img/placeholders/avatar.webp',
        ];
    }
}
