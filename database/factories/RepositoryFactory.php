<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repository>
 */
class RepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_active' => true,
            'image' => '/img/placeholders/avatar.webp',
            'url' => fake()->url(),
            'type' => fake()->randomElement(['tutorial', 'package', 'software']),
            'name' => fake()->userName(),
        ];
    }
}
