<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Music;

class MusicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Music::factory(5)->create();
    }
}
