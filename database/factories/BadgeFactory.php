<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Badge>
 */
class BadgeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'code' => Str::slug($name),
            'name' => $name,
            'description' => fake()->sentence(),
            'image' => '/img/placeholders/badge.webp',
            'type' => Badge::TYPE_FIXED,
            'source' => fake()->optional()->word(),
            'rule' => null,
            'is_active' => true,
        ];
    }

    public function fixed(): static
    {
        return $this->state(fn () => [
            'type' => Badge::TYPE_FIXED,
        ]);
    }

    public function stealable(): static
    {
        return $this->state(fn () => [
            'type' => Badge::TYPE_STEALABLE,
        ]);
    }
}
