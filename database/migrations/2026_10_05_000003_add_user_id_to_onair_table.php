<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onairs', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('program_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('onairs')
            ->select(['id', 'program_id'])
            ->whereNull('user_id')
            ->orderBy('id')
            ->chunkById(100, function ($onairs): void {
                $programUserIds = DB::table('programs')
                    ->whereIn('id', $onairs->pluck('program_id')->unique()->all())
                    ->pluck('user_id', 'id');

                foreach ($onairs as $onair) {
                    $userId = $programUserIds[$onair->program_id] ?? null;

                    if ($userId) {
                        DB::table('onairs')
                            ->where('id', $onair->id)
                            ->update(['user_id' => $userId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('onairs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
