<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DUPLICATE_WINDOW_SECONDS = 60;

    public function up(): void
    {
        $lastSeenAt = [];
        $duplicateIds = [];

        DB::table('page_views')
            ->select([
                'id',
                'actor_type',
                'actor_id',
                'ip_address',
                'viewable_type',
                'viewable_id',
                'page_name',
                'page_url',
                'created_at',
            ])
            ->whereNotNull('created_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->each(function (object $view) use (&$lastSeenAt, &$duplicateIds): void {
                $fingerprint = hash('sha256', json_encode([
                    'actor_type' => $view->actor_type,
                    'actor_id' => $view->actor_id,
                    'ip_address' => $view->ip_address,
                    'viewable_type' => $view->viewable_type,
                    'viewable_id' => $view->viewable_id,
                    'page_name' => $view->page_name,
                    'page_url' => $view->page_url,
                ], JSON_THROW_ON_ERROR));
                $createdAt = Carbon::parse($view->created_at);
                $previousAt = $lastSeenAt[$fingerprint] ?? null;

                if ($previousAt
                    && $createdAt->getTimestamp() - $previousAt->getTimestamp() < self::DUPLICATE_WINDOW_SECONDS) {
                    $duplicateIds[] = $view->id;
                }

                // Updating on every record removes an uninterrupted polling chain,
                // while a gap of 60 seconds starts a new legitimate visit.
                $lastSeenAt[$fingerprint] = $createdAt;
            });

        foreach (array_chunk($duplicateIds, 1000) as $ids) {
            DB::table('page_views')->whereIn('id', $ids)->delete();
        }
    }

    public function down(): void
    {
        // Registros inflados removidos não podem ser reconstruídos com segurança.
    }
};
