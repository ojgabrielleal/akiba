<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->select('id')->get();

        foreach ($users as $user) {
            $hasThreads = DB::table('user_socials')
                ->where('user_id', $user->id)
                ->where('name', 'Threads')
                ->exists();

            if (!$hasThreads) {
                DB::table('user_socials')->insert([
                    'uuid' => (string) str()->uuid(),
                    'user_id' => $user->id,
                    'name' => 'Threads',
                    'url' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('user_socials')
            ->where('name', 'Threads')
            ->whereNull('url')
            ->delete();
    }
};
