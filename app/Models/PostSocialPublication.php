<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostSocialPublication extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'post_id',
        'caption',
        'hashtags',
        'artwork_path',
        'requested_at',
    ];

    protected $hidden = [
        'post_id',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'requested_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function results()
    {
        return $this->hasMany(PostSocialPublicationResult::class, 'post_social_publication_id');
    }
}
