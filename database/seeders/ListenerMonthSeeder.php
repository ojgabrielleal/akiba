<?php

namespace Database\Seeders;

use App\Models\ListenerMonth;
use App\Models\OAuthAccount;
use Illuminate\Database\Seeder;

class ListenerMonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oauthAccount = OAuthAccount::query()->first()
            ?? OAuthAccount::factory()->create();

        ListenerMonth::factory()
            ->for($oauthAccount, 'oauthAccount')
            ->create();
    }
}
