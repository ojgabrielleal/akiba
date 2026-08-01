<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PostLike extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'post_id',
        'liker_type',
        'liker_id',
        'visitor_token',
    ];

    protected $hidden = [
        'post_id',
        'liker_type',
        'liker_id',
    ];

    /**
     * Determine the columns that should receive a unique identifier.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function liker(): MorphTo
    {
        return $this->morphTo();
    }
}
