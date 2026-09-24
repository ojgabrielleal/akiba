<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Music extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'anime_id',
        'type',
        'artist',
        'name',
        'is_manual',
        'in_ranking',
        'image_ranking',
        'song_requests_total',
    ];

    protected $hidden = [
        'anime_id',
    ];

    protected $casts = [
        'is_manual' => 'boolean',
        'in_ranking' => 'boolean',
        'song_requests_total' => 'integer',
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
     * Query scopes for this model.
     *
     * These methods define reusable query filters that can be
     * applied to Eloquent queries (e.g., active()).
     */
    #[Scope]
    protected function inRanking(Builder $query): void
    {
        $query->where('in_ranking', true)
            ->where('is_manual', false);
    }

    #[Scope]
    protected function automatic(Builder $query): void
    {
        $query->where('is_manual', false);
    }

    /**
     * Static query methods for this model.
     *
     * These methods encapsulate complete query logic and business
     * rules that return finalized results, such as reports,
     * aggregations, or ranked lookups.
     */

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class, 'anime_id');
    }

    public static function mostRequested(int $limit = 3)
    {
        return self::with('anime')->automatic()->orderBy('song_requests_total', 'desc')
            ->limit($limit)
            ->get();
    }
}
