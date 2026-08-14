<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opinions', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->dropForeign(['review_id']);
            } else {
                $table->dropForeign('reviews_contents_review_id_foreign');
            }

            $table->dropColumn('review_id');
            $table->foreignId('post_id')->after('user_id')->constrained('posts')->cascadeOnDelete();
        });

        Schema::rename('opinions', 'post_reviews');

        Schema::dropIfExists('events');
        Schema::dropIfExists('reviews');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('post_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->date('year_of_release')->nullable();
            $table->longText('sinopse');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('post_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->string('dates');
            $table->string('address');
            $table->timestamps();
        });

        Schema::rename('post_reviews', 'opinions');

        Schema::table('opinions', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropColumn('post_id');
            $table->foreignId('review_id')->after('user_id')->constrained('reviews')->cascadeOnDelete();
        });
    }
};
