<?php

namespace Database\Factories;

use App\Models\Anime;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Anime>
 */
class AnimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'image' => '/img/placeholders/avatar.webp',
            'discography_status' => fake()->randomElement([
                Anime::DISCOGRAPHY_UNKNOWN,
                Anime::DISCOGRAPHY_PARTIAL,
                Anime::DISCOGRAPHY_COMPLETE,
            ]),
            'discography_checked_at' => fake()->optional()->dateTimeBetween('-1 month'),
        ];
    }

    public function completeDiscography(): static
    {
        return $this->state(fn () => [
            'discography_status' => Anime::DISCOGRAPHY_COMPLETE,
            'discography_checked_at' => now(),
        ]);
    }
}
