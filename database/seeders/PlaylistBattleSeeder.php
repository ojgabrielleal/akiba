<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PlaylistBattle;

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
