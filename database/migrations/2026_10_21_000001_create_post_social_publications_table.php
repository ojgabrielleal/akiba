<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        Schema::dropIfExists('post_social_publications');
    }
};
