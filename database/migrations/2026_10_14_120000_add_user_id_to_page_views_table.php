<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropIndex('page_views_actor_type_actor_id_index');
            $table->dropColumn('actor_type');
            $table->renameColumn('actor_id', 'user_id');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
