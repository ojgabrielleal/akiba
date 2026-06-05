<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Program extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'programs';

    protected $fillable = [
        'uuid',
        'is_active',
        'user_id',
        'name',
        'image',
        'access_type',
        'execution_mode',
        'is_default_auto_dj',
        'phrases',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default_auto_dj' => 'boolean',
        'phrases' => 'array',
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
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailableForLocution($query, User $user)
    {
        return $query
            ->active()
            ->where('execution_mode', 'live')
            ->where(function ($q) use ($user) {
                $q->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->where('access_type', 'private');
                })->orWhere('access_type', 'free');
            });
    }

    /**
     * Define the relationships between this model and other models.
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function onair()
    {
        return $this->hasMany(Onair::class, 'program_id');
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function airtimes()
    {
        return $this->hasMany(Airtime::class, 'program_id');
    }

    public function plans()
    {
        return $this->morphMany(Plan::class, 'plannable');
    }
}
