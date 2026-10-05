<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('podcast_listens', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('podcast_id')->constrained()->cascadeOnDelete();
            $table->morphs('listener');
            $table->timestamp('listened_at')->useCurrent();
            $table->timestamps();

            $table->unique(['podcast_id', 'listener_type', 'listener_id'], 'podcast_listens_unique_listener');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podcast_listens');
    }
};
