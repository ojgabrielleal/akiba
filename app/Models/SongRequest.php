<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'was_reproduced',
        'was_canceled',
        'onair_id',
        'music_id',
        'ip_address',
        'name',
        'address',
        'message',
    ];

    protected $hidden = [
        'onair_id',
        'music_id'
    ];

    protected $casts = [
        'was_reproduced' => 'boolean',
        'was_canceled' => 'boolean'
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
}
