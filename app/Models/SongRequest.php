<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SongRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'type',
        'was_reproduced',
        'was_canceled',
        'was_read',
        'was_dismissed',
        'onair_id',
        'music_id',
        'requester_type',
        'requester_id',
        'message',
    ];

    protected $hidden = [
        'onair_id',
        'music_id',
        'requester_type',
        'requester_id',
    ];

    protected $casts = [
        'type' => 'string',
        'was_reproduced' => 'boolean',
        'was_canceled' => 'boolean',
        'was_read' => 'boolean',
        'was_dismissed' => 'boolean',
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
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function onair()
    {
        return $this->belongsTo(Onair::class, 'onair_id');
    }

    public function music()
    {
        return $this->belongsTo(Music::class, 'music_id');
    }

    public function requester(): MorphTo
    {
        return $this->morphTo();
    }

}
