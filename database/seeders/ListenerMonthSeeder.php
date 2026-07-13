<?php

namespace Database\Seeders;

use App\Models\ListenerMonth;
use Illuminate\Database\Seeder;

class ListenerMonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ListenerMonth::factory()->create();
    }
}
