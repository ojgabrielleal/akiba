<?php

namespace Database\Seeders;

use App\Models\Music;
use App\Models\Onair;
use App\Models\SongRequest;
use Illuminate\Database\Seeder;

class SongRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SongRequest::exists()) return;

        $onairs = Onair::inRandomOrder()->take(5)->get();
        $music = Music::inRandomOrder()->first();

        if ($onairs->isEmpty() || !$music) return;

        foreach ($onairs as $onair) {
            SongRequest::factory(5)
                ->for($onair, 'onair')
                ->for($music, 'music')
                ->create();
        }
    }
}
