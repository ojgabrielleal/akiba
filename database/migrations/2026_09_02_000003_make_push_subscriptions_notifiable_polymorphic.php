<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->nullableMorphs('notifiable');
        });

        DB::table('push_subscriptions')
            ->whereNotNull('user_id')
            ->update(['notifiable_type' => User::class]);

        DB::table('push_subscriptions')
            ->whereNotNull('user_id')
            ->update(['notifiable_id' => DB::raw('user_id')]);

        Schema::table('push_subscriptions', function (Blueprint $table): void {
            if ($this->indexExists('push_subscriptions', 'push_subscriptions_user_id_endpoint_unique')) {
                $table->dropUnique('push_subscriptions_user_id_endpoint_unique');
            }
            $table->dropConstrainedForeignId('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('uuid')->constrained()->cascadeOnDelete();
        });

        DB::table('push_subscriptions')
            ->where('notifiable_type', User::class)
            ->update(['user_id' => DB::raw('notifiable_id')]);

        Schema::table('push_subscriptions', function (Blueprint $table): void {
            if (! $this->indexExists('push_subscriptions', 'push_subscriptions_user_id_endpoint_unique')
                && ! $this->indexExists('push_subscriptions', 'push_subscriptions_endpoint_unique')) {
                $table->unique(['user_id', 'endpoint']);
            }

            $table->dropMorphs('notifiable');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }
};
