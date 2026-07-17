<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory, HasUuids;


    protected $fillable = [
        'uuid',
        'user_id',
        'allows_confirmations',
        'limit',
        'title',
        'content',
    ];

    protected $casts = [
        'allows_confirmations' => 'boolean',
        'limit' => 'date:Y-m-d',
    ];

    protected $hidden = [
        'user_id',
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
    protected function notExpired(Builder $query): void
    {
        $query->where('limit', '>=', today());
    }

    /**
     * Define the relationships between this model and other models.
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function confirmations()
    {
        return $this->belongsToMany(User::class, 'activity_pivot', 'activity_id', 'user_id');
    }

    public function calendar()
    {
        return $this->hasOne(Calendar::class, 'activity_id');
    }

}
