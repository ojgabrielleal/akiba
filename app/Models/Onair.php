<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onair extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'in_air',
        'program_id',
        'paused_plan_id',
        'phrase',
        'execution_mode',
        'icon',
        'allows_song_requests',
        'song_requests_total'
    ];

    protected $casts = [
        'allows_song_requests' => 'boolean',
        'in_air' => 'boolean',
        'phrase' => 'array',
        'song_requests_total' => 'integer',
    ];

    protected $hidden = [
        'show_id'
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
    protected function live(Builder $query): void
    {
        $query->where('in_air', true);
    }

    /**
     * Define the relationships between this model and other models.
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function pausedPlan()
    {
        return $this->belongsTo(Plan::class, 'paused_plan_id');
    }

    public function songRequests()
    {
        return $this->hasMany(SongRequest::class, 'onair_id');
    }
}
