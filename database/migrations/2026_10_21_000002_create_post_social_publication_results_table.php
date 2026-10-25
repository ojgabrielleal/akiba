<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_social_publication_results', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('post_social_publication_id');
            $table->foreign('post_social_publication_id', 'post_social_publication_result_fk')
                ->references('id')
                ->on('post_social_publications')
                ->cascadeOnDelete();
            $table->string('channel', 32);
            $table->string('status', 32)->default('pending');
            $table->string('external_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['post_social_publication_id', 'channel'],
                'post_social_publication_channel_unique',
            );
            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_social_publication_results');
    }
};
