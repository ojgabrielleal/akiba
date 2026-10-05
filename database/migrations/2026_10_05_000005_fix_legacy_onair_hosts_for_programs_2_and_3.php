<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('onairs')
            ->whereIn('program_id', [2, 3])
            ->update(['user_id' => 6]);
    }

    public function down(): void
    {
        DB::table('onairs')
            ->whereIn('program_id', [2, 3])
            ->where('user_id', 6)
            ->update(['user_id' => null]);
    }
};
