<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('posts')
                ->where('module', 'review')
                ->orderBy('id')
                ->each(function (object $post): void {
                    $metadata = json_decode($post->metadata ?? '[]', true) ?: [];

                    if (filled($post->studio ?? null)) {
                        $metadata['studio'] = $post->studio;
                    }

                    DB::table('posts')
                        ->where('id', $post->id)
                        ->update(['metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE)]);
                });

            $referencesTable = Schema::hasTable('post_references') ? 'post_references' : 'references';

            if (Schema::hasTable($referencesTable)) {
                DB::table($referencesTable)
                    ->whereIn('post_id', DB::table('posts')->select('id')->where('module', 'review'))
                    ->delete();
            }
        });

        Schema::table('posts', function (Blueprint $table): void {
            if (Schema::hasColumn('posts', 'studio')) {
                $table->dropColumn('studio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('posts', 'studio')) {
                $table->string('studio')->nullable()->after('metadata');
            }
        });

        DB::table('posts')
            ->where('module', 'review')
            ->orderBy('id')
            ->each(function (object $post): void {
                $metadata = json_decode($post->metadata ?? '[]', true) ?: [];

                DB::table('posts')
                    ->where('id', $post->id)
                    ->update(['studio' => $metadata['studio'] ?? null]);
            });
    }
};
