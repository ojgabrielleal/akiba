<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_social_publication_results', function (Blueprint $table): void {
            $table->index(
                'post_social_publication_id',
                'post_social_publication_result_publication_index',
            );
        });

        Schema::table('post_social_publication_results', function (Blueprint $table): void {
            $table->dropUnique('post_social_publication_channel_unique');
            $table->string('placement', 32)->default('feed')->after('channel');
            $table->string('artwork_path', 2048)->nullable()->after('placement');
            $table->unique(
                ['post_social_publication_id', 'channel', 'placement'],
                'post_social_publication_channel_placement_unique',
            );
            $table->index(['channel', 'placement', 'status']);
        });

        DB::table('post_social_publication_results')
            ->orderBy('id')
            ->eachById(function (object $result): void {
                $artworkPath = DB::table('post_social_publications')
                    ->where('id', $result->post_social_publication_id)
                    ->value('artwork_path');

                DB::table('post_social_publication_results')
                    ->where('id', $result->id)
                    ->update(['artwork_path' => $artworkPath]);
            });
    }

    public function down(): void
    {
        Schema::table('post_social_publication_results', function (Blueprint $table): void {
            $table->dropUnique('post_social_publication_channel_placement_unique');
            $table->dropIndex(['channel', 'placement', 'status']);
            $table->dropColumn(['placement', 'artwork_path']);
            $table->unique(
                ['post_social_publication_id', 'channel'],
                'post_social_publication_channel_unique',
            );
        });

        Schema::table('post_social_publication_results', function (Blueprint $table): void {
            $table->dropIndex('post_social_publication_result_publication_index');
        });
    }
};
