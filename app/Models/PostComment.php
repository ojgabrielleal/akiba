<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PostComment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'post_id',
        'author_type',
        'author_id',
        'comment',
    ];

    protected $hidden = [
        'post_id',
        'author_type',
        'author_id',
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

    public function author(): MorphTo
    {
        return $this->morphTo();
    }
}
