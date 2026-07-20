<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('onair', 'paused_plan_id') && DB::getDriverName() !== 'sqlite') {
            Schema::table('onair', function (Blueprint $table) {
                $table->dropForeign(['paused_plan_id']);
            });
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex(['plannable_type', 'plannable_id']);
        });

        Schema::rename('plans', 'program_schedules');

        Schema::table('program_schedules', function (Blueprint $table) {
            $table->renameColumn('plannable_id', 'program_id');
        });

        Schema::table('program_schedules', function (Blueprint $table) {
            $table->dropColumn('plannable_type');
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
        });

        if (Schema::hasColumn('onair', 'paused_plan_id')) {
            Schema::table('onair', function (Blueprint $table) {
                $table->renameColumn('paused_plan_id', 'paused_program_schedule_id');
            });
        }

        if (Schema::hasColumn('onair', 'paused_program_schedule_id') && DB::getDriverName() !== 'sqlite') {
            Schema::table('onair', function (Blueprint $table) {
                $table->foreign('paused_program_schedule_id')
                    ->references('id')
                    ->on('program_schedules')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('onair', 'paused_program_schedule_id') && DB::getDriverName() !== 'sqlite') {
            Schema::table('onair', function (Blueprint $table) {
                $table->dropForeign(['paused_program_schedule_id']);
            });
        }

        if (Schema::hasColumn('onair', 'paused_program_schedule_id')) {
            Schema::table('onair', function (Blueprint $table) {
                $table->renameColumn('paused_program_schedule_id', 'paused_plan_id');
            });
        }

        Schema::table('program_schedules', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->string('plannable_type')->default('App\\Models\\Program')->after('user_id');
        });

        Schema::table('program_schedules', function (Blueprint $table) {
            $table->renameColumn('program_id', 'plannable_id');
        });

        Schema::table('program_schedules', function (Blueprint $table) {
            $table->index(['plannable_type', 'plannable_id']);
        });

        Schema::rename('program_schedules', 'plans');

        if (Schema::hasColumn('onair', 'paused_plan_id') && DB::getDriverName() !== 'sqlite') {
            Schema::table('onair', function (Blueprint $table) {
                $table->foreign('paused_plan_id')->references('id')->on('plans')->nullOnDelete();
            });
        }
    }
};
