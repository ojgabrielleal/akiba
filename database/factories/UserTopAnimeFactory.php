<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserTopAnime>
 */
class UserTopAnimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'user_id' => User::factory(),
            'position' => fake()->numberBetween(1, 10),
            'anime_theme_list_id' => fake()->uuid(),
            'slug' => fake()->slug(),
            'name' => $name,
            'image' => '/img/placeholders/avatar.webp',
            'metadata' => [
                'year' => fake()->year(),
                'title' => $name,
            ],
        ];
    }
}
