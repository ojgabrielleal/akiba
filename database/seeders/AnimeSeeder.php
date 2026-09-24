<?php

namespace Database\Seeders;

use App\Models\Anime;
use Illuminate\Database\Seeder;

class AnimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Anime::factory(5)->create();
    }
}
