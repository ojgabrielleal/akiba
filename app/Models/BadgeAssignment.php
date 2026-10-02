<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BadgeAssignment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'badge_id',
        'owner_type',
        'owner_id',
        'awarded_by_type',
        'awarded_by_id',
        'reason',
        'metadata',
        'acquired_at',
        'revoked_at',
        'revoked_reason',
    ];

    protected $casts = [
        'metadata' => 'array',
        'acquired_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNull('revoked_at');
    }

    #[Scope]
    protected function revoked(Builder $query): void
    {
        $query->whereNotNull('revoked_at');
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function awardedBy(): MorphTo
    {
        return $this->morphTo();
    }
}
