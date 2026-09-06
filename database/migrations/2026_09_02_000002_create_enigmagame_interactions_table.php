<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enigmagame_interactions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('enigmagame_id')->constrained()->cascadeOnDelete();
            $table->morphs('participant');
            $table->string('type')->index();
            $table->text('content');
            $table->text('admin_response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('response_notified_at')->nullable();
            $table->timestamps();

            $table->index(['enigmagame_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enigmagame_interactions');
    }
};
