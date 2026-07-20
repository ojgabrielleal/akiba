<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('program_schedules')
            ->where('action', 'finish_program')
            ->whereIn('status', ['pending', 'running'])
            ->update(['status' => 'cancelled']);
    }

    public function down(): void
    {
        DB::table('program_schedules')
            ->where('action', 'finish_program')
            ->where('status', 'cancelled')
            ->update(['status' => 'pending']);
    }
};
