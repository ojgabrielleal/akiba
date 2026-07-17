<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, HasUuids;


    protected $fillable = [
        'uuid',
        'user_id',
        'plannable_type',
        'plannable_id',
        'action',
        'scheduled_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    protected $hidden = [
        'user_id',
        'plannable_type',
        'plannable_id',
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
     * applied to Eloquent queries (e.g., pendingExecution()).
     */
    #[Scope]
    protected function pendingExecution(Builder $query): void
    {
        $query->whereIn('status', ['pending', 'running', 'paused']);
    }

    /**
     * Define the relationships between this model and other models.
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plannable()
    {
        return $this->morphTo();
    }
}
