<?php

namespace App\Models;

use Carbon\Carbon;

use App\Models\OAuthAccount;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListenerMonth extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'oauth_account_id',
        'favorite_program',
        'favorite_music',
        'requests_total',
    ];

    protected $casts = [
        'requests_total' => 'integer',
        'favorite_program' => 'array',
        'favorite_music' => 'array',
    ];

    /**
     * Determine the columns that should receive a unique identifier.
     *
     * This method specifies that the 'uuid' column should be automatically 
     * generated as a sortable, unique identifier when the model is created.
     *
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Define the relationships between this model and other models.
     */
    public function oauthAccount()
    {
        return $this->belongsTo(OAuthAccount::class, 'oauth_account_id');
    }

    /**
     * Static query methods for this model.
     *
     * These methods encapsulate complete query logic and business
     * rules that return finalized results, such as reports,
     * aggregations, or single-record lookups. Unlike query scopes,
     * they execute the query internally (e.g., first(), get())
     * and are intended to be called directly on the model.
     *
     */
    public static function mostActiveListenerOfCurrentMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $listener = DB::selectOne('
            SELECT
                song_requests.requester_id AS oauth_account_id,
                COUNT(*) AS requests_total
            FROM song_requests
            WHERE song_requests.created_at BETWEEN ? AND ?
                AND song_requests.requester_type = ?
                AND song_requests.requester_id IS NOT NULL
            GROUP BY song_requests.requester_id
            ORDER BY requests_total DESC
            LIMIT 1
        ', [$startOfMonth, $endOfMonth, OAuthAccount::class]);

        if (!$listener) {
            return null;
        }

        $program = DB::selectOne('
            SELECT 
                programs.name AS name,
                programs.image AS image,
                COUNT(*) AS requests_total
            FROM song_requests
            JOIN onairs ON song_requests.onair_id = onairs.id
            JOIN programs ON onairs.program_id = programs.id 
            WHERE song_requests.created_at BETWEEN ? AND ? 
                AND song_requests.requester_type = ?
                AND song_requests.requester_id = ?
            GROUP BY
                programs.name,
                programs.image 
            ORDER BY requests_total DESC
            LIMIT 1
        ', [$startOfMonth, $endOfMonth, OAuthAccount::class, $listener->oauth_account_id]);

        $music = DB::selectOne('
            SELECT 
                music.name AS name,
                music.artist AS artist,
                music.production AS production,
                music.image AS image,
                COUNT(*) AS requests_total
            FROM song_requests
            JOIN music ON song_requests.music_id = music.id 
            WHERE song_requests.created_at BETWEEN ? AND ? 
                AND song_requests.requester_type = ?
                AND song_requests.requester_id = ?
            GROUP BY 
                music.name, 
                music.artist,
                music.production,
                music.image
            ORDER BY requests_total DESC
            LIMIT 1
        ', [$startOfMonth, $endOfMonth, OAuthAccount::class, $listener->oauth_account_id]);

        return (object) [
            'oauth_account_id' => $listener->oauth_account_id,
            'requests_total' => $listener->requests_total,
            'favorite_program' => [
                'name' => $program?->name,
                'image' => $program?->image,
            ],
            'favorite_music' => [
                'name' => $music?->name,
                'artist' => $music?->artist,
                'production' => $music?->production,
                'image' => $music?->image,
            ],
        ];
    }
}
