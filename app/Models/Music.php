<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Music extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'musics';

    protected $fillable = [
        'uuid',
        'type',
        'production',
        'image',
        'artist',
        'name',
        'in_ranking',
        'image_ranking',
        'song_requests_total',
    ];

    protected $casts = [
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
    public function scopeRanking($query)
    {
        return $query->where('in_ranking', true);
    }
}
