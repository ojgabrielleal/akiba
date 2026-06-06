<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Preference extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'preferences';

    protected $fillable = [
        'uuid',
        'user_id',
        'is_like',
        'content'
    ];

    protected $casts = [
        'is_like' => 'boolean'
    ];

    protected $hidden = [
        'user_id'
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
