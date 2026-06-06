<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Option extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'options';

    protected $fillable = [
        'uuid',
        'poll_id',
        'option',
        'votes',
    ];

    protected $hidden = [
        'poll_id'
    ];

    protected $casts = [
        'votes' => 'integer',
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

    public function poll()
    {
        return $this->belongsTo(Poll::class, 'poll_id');
    }
}
