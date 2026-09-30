<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_schedules', function (Blueprint $table) {
            $table->index(['status', 'action', 'scheduled_at', 'id'], 'program_schedules_due_index');
        });

        Schema::table('onairs', function (Blueprint $table) {
            $table->index(['in_air', 'execution_mode', 'id'], 'onairs_live_mode_index');
        });
    }

    public function down(): void
    {
        Schema::table('program_schedules', function (Blueprint $table) {
            $table->dropIndex('program_schedules_due_index');
        });

        Schema::table('onairs', function (Blueprint $table) {
            $table->dropIndex('onairs_live_mode_index');
        });
    }
};
