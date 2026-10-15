<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'actor_id');
            $table->string('actor_type')->nullable()->after('actor_id');
            $table->index(['actor_type', 'actor_id']);
            DB::table('page_views')->whereNotNull('actor_id')->update([
                'actor_type' => App\Models\User::class,
            ]);
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
