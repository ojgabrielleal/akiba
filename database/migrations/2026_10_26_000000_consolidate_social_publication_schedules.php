<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_publication_schedules', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('publishable_type');
            $table->unsignedBigInteger('publishable_id');
            $table->string('channel', 32);
            $table->string('placement', 32);
            $table->json('payload');
            $table->string('media_path', 2048)->nullable();
            $table->string('media_url', 2048);
            $table->string('status', 32)->default('pending');
            $table->string('idempotency_key', 255)->unique();
            $table->string('external_id')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['publishable_type', 'publishable_id'], 'social_publication_publishable_index');
            $table->index(['status', 'scheduled_at'], 'social_publication_due_index');
            $table->index(['channel', 'status'], 'social_publication_channel_status_index');
        });

        $this->migratePostPublications();

        Schema::dropIfExists('post_social_publication_results');
        Schema::dropIfExists('post_social_publications');
    }

    public function down(): void
    {
        Schema::create('post_social_publications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->text('caption');
            $table->json('hashtags');
            $table->string('artwork_path', 2048);
            $table->timestamp('requested_at');
            $table->timestamps();
            $table->index(['post_id', 'requested_at']);
        });

        Schema::create('post_social_publication_results', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('post_social_publication_id');
            $table->foreign('post_social_publication_id', 'post_social_publication_result_fk')
                ->references('id')->on('post_social_publications')->cascadeOnDelete();
            $table->string('channel', 32);
            $table->string('placement', 32)->default('feed');
            $table->string('artwork_path', 2048)->nullable();
            $table->string('status', 32)->default('pending');
            $table->string('external_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['post_social_publication_id', 'channel', 'placement'],
                'post_social_publication_channel_placement_unique',
            );
            $table->index('post_social_publication_id', 'post_social_publication_result_publication_index');
            $table->index(['channel', 'placement', 'status']);
        });

        Schema::dropIfExists('social_publication_schedules');
    }

    private function migratePostPublications(): void
    {
        if (! Schema::hasTable('post_social_publications') || ! Schema::hasTable('post_social_publication_results')) {
            return;
        }

        DB::table('post_social_publication_results as result')
            ->join('post_social_publications as publication', 'publication.id', '=', 'result.post_social_publication_id')
            ->join('posts as post', 'post.id', '=', 'publication.post_id')
            ->select([
                'result.*',
                'publication.uuid as publication_uuid',
                'publication.post_id',
                'publication.caption',
                'publication.hashtags',
                'publication.artwork_path as publication_artwork_path',
                'publication.requested_at',
                'post.uuid as post_uuid',
            ])
            ->orderBy('result.id')
            ->each(function (object $result): void {
                $mediaUrl = $result->artwork_path ?: $result->publication_artwork_path;
                $urlPath = parse_url($mediaUrl, PHP_URL_PATH);
                $mediaPath = is_string($urlPath) && str_starts_with($urlPath, '/storage/')
                    ? substr($urlPath, strlen('/storage/'))
                    : null;

                DB::table('social_publication_schedules')->insert([
                    'id' => $result->id,
                    'uuid' => $result->uuid,
                    'publishable_type' => Post::class,
                    'publishable_id' => $result->post_id,
                    'channel' => $result->channel,
                    'placement' => $result->placement,
                    'payload' => json_encode([
                        'caption' => $result->caption,
                        'hashtags' => json_decode($result->hashtags, true) ?: [],
                        'post_url' => '',
                    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    'media_path' => $mediaPath,
                    'media_url' => $mediaUrl,
                    'status' => $result->status === 'processing' ? 'pending' : $result->status,
                    'idempotency_key' => implode(':', [
                        'social',
                        'post',
                        $result->post_uuid,
                        $result->publication_uuid,
                        $result->channel,
                        $result->placement,
                    ]),
                    'external_id' => $result->external_id,
                    'last_error' => $result->error,
                    'scheduled_at' => $result->requested_at,
                    'published_at' => $result->published_at,
                    'created_at' => $result->created_at,
                    'updated_at' => $result->updated_at,
                ]);
            });
    }
};
