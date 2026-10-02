<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enigmagames', function (Blueprint $table): void {
            $table->text('image')->nullable()->after('content');
        });

        DB::table('enigmagames')->update([
            'image' => DB::raw('content'),
            'content' => '',
        ]);
    }

    public function down(): void
    {
        DB::table('enigmagames')
            ->whereNotNull('image')
            ->update(['content' => DB::raw('image')]);

        Schema::table('enigmagames', function (Blueprint $table): void {
            $table->dropColumn('image');
        });
    }
};
