<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialPublicationSchedule extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'uuid',
        'publishable_type',
        'publishable_id',
        'channel',
        'placement',
        'payload',
        'media_path',
        'media_url',
        'status',
        'idempotency_key',
        'external_id',
        'last_error',
        'scheduled_at',
        'published_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $hidden = [
        'publishable_id',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    public function publishable()
    {
        return $this->morphTo();
    }
}
