<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Favority extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'favorities';

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'image',
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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
