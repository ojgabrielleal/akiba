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
            ->where('status', 'paused')
            ->update(['status' => 'running']);

        if (Schema::hasColumn('onair', 'paused_program_schedule_id')) {
            Schema::table('onair', function (Blueprint $table) {
                if (DB::getDriverName() === 'sqlite') {
                    $table->dropColumn('paused_program_schedule_id');
                } else {
                    $table->dropConstrainedForeignId('paused_program_schedule_id');
                }
            });
        }

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

    public function down(): void
    {
        Schema::table('program_schedules', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'running',
                'paused',
                'completed',
                'cancelled',
                'failed',
                'expired',
            ])->default('pending')->change();
        });

        Schema::table('onair', function (Blueprint $table) {
            $table->foreignId('paused_program_schedule_id')
                ->nullable()
                ->after('program_id')
                ->constrained('program_schedules')
                ->nullOnDelete();
        });
    }
};
