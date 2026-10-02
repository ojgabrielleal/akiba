<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_assignments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->morphs('owner');
            $table->nullableMorphs('awarded_by');
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('acquired_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoked_reason')->nullable();
            $table->timestamps();

            $table->index(['badge_id', 'revoked_at']);
            $table->index(['owner_type', 'owner_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_assignments');
    }
};
