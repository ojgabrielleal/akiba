<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class ListenerMonth extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'avatar',
        'address',
        'birthday',
        'favorite_program',
        'favorite_music',
        'requests_total',
    ];

    protected $casts = [
        'birthday' => 'date:Y-m-d',
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
                song_requests.name AS name,
                song_requests.address AS address,
                COUNT(*) AS requests_total
            FROM song_requests
            WHERE song_requests.created_at BETWEEN ? AND ?
            GROUP BY 
                song_requests.name,
                song_requests.address
            ORDER BY requests_total DESC
            LIMIT 1
        ', [$startOfMonth, $endOfMonth]);

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
                AND song_requests.name = ? 
                AND song_requests.address = ?
            GROUP BY
                programs.name,
                programs.image 
            ORDER BY requests_total DESC
            LIMIT 1
        ', [$startOfMonth, $endOfMonth, $listener->name, $listener->address]);

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
                AND song_requests.name = ? 
                AND song_requests.address = ?
            GROUP BY 
                music.name, 
                music.artist,
                music.production,
                music.image
            ORDER BY requests_total DESC
            LIMIT 1
        ', [$startOfMonth, $endOfMonth, $listener->name, $listener->address]);

        return (object) [
            'name' => $listener->name,
            'address' => $listener->address,
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
