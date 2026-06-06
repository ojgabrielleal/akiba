<?php

namespace Database\Factories;

use Database\Factories\Concerns\HasFakeImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlaylistBattle>
 */
class PlaylistBattleFactory extends Factory
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
            'day' => fake()->randomElement([0, 1, 2, 3, 4, 5, 6]),
            'image' => $this->fakeImageUrl(),
        ];
    }
}
