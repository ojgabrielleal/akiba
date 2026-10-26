<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'viewable_type',
        'viewable_id',
        'ip_address',
        'country_code',
        'region_code',
        'city',
        'latitude',
        'longitude',
        'location_accuracy_radius',
        'timezone',
        'page_name',
        'page_url',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'location_accuracy_radius' => 'integer',
    ];

    protected $hidden = ['actor_type', 'actor_id', 'ip_address'];

    public function actor()
    {
        return $this->morphTo();
    }

    /**
     * Define the relationships between this model and other models.
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function viewable()
    {
        return $this->morphTo();
    }
}
