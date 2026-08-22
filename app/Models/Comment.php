<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_VISIBLE = 'visible';
    public const STATUS_PENDING = 'pending';
    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'uuid',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'author_type',
        'author_id',
        'comment',
        'status',
        'moderated_by',
        'moderated_at',
        'moderation_reason',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
    ];

    protected $hidden = [
        'commentable_type',
        'commentable_id',
        'parent_id',
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

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->oldest();
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('status', self::STATUS_VISIBLE);
    }
}
