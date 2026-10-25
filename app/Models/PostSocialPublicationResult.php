<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostSocialPublicationResult extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'post_social_publication_id',
        'channel',
        'placement',
        'artwork_path',
        'status',
        'external_id',
        'attempts',
        'error',
        'published_at',
    ];

    protected $hidden = [
        'post_social_publication_id',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'published_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function publication()
    {
        return $this->belongsTo(PostSocialPublication::class, 'post_social_publication_id');
    }
}
