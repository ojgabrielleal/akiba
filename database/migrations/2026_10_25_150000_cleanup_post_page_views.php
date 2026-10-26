<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $postType = (new Post)->getMorphClass();
        $userType = (new User)->getMorphClass();

        DB::table('page_views')
            ->where('viewable_type', $postType)
            ->where('created_at', '<', '2026-10-09 00:00:00')
            ->delete();

        DB::table('page_views')
            ->where('viewable_type', $postType)
            ->where('actor_type', $userType)
            ->whereExists(function (Builder $query): void {
                $query
                    ->selectRaw('1')
                    ->from('posts')
                    ->whereColumn('posts.id', 'page_views.viewable_id')
                    ->whereColumn('posts.user_id', 'page_views.actor_id');
            })
            ->delete();
    }

    public function down(): void
    {
        // Dados de visualização removidos não podem ser reconstruídos com segurança.
    }
};
