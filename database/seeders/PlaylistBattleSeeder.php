<?php

namespace Database\Seeders;

use App\Models\PlaylistBattle;
use Illuminate\Database\Seeder;

class PlaylistBattleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlaylistBattle::factory()->create();
    }
}
