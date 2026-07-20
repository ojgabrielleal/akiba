<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('program_schedules')
            ->where('status', 'running')
            ->update(['status' => 'completed']);

        Schema::table('program_schedules', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'completed',
                'cancelled',
                'failed',
                'expired',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('program_schedules', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'running',
                'completed',
                'cancelled',
                'failed',
                'expired',
            ])->default('pending')->change();
        });
    }
};
