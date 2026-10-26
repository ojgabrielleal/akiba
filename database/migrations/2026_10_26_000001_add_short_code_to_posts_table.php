<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('short_code', 8)->nullable()->unique()->after('slug');
        });

        DB::table('posts')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($posts): void {
                foreach ($posts as $post) {
                    DB::table('posts')
                        ->where('id', $post->id)
                        ->update(['short_code' => $this->generateShortCode()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropUnique(['short_code']);
            $table->dropColumn('short_code');
        });
    }

    private function generateShortCode(): string
    {
        do {
            $shortCode = Str::lower(Str::random(8));
        } while (DB::table('posts')->where('short_code', $shortCode)->exists());

        return $shortCode;
    }
};
