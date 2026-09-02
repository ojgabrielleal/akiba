<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, HasUuids;


    protected $fillable = [
        'uuid',
        'user_id',
        'is_active',
        'status',
        'image',
        'slug',
        'title',
        'content',
        'cover',
        'module',
        'metadata',
        'studio',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => [
                'title' => $value,
                'slug' => Str::slug($value),
            ],
        );
    }

    protected function reviewReleaseDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->module === 'review' && ($this->metadata['date_of_release'] ?? $this->metadata['year_of_release'] ?? null)
                ? Carbon::parse($this->metadata['date_of_release'] ?? $this->metadata['year_of_release'])
                : null,
        );
    }

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
     * Query scopes for this model.
     *
     * These methods define reusable query filters that can be
     * applied to Eloquent queries (e.g., active()).
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function withStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    #[Scope]
    protected function authoredBy(Builder $query, User $user): void
    {
        $query->whereBelongsTo($user, 'author');
    }

    #[Scope]
    protected function orderByMostViewed(Builder $query): void
    {
        $query->orderByDesc('views_count');
    }

    #[Scope]
    protected function forModule(Builder $query, string $module): void
    {
        $query->where('module', $module);
    }
    
    /**
     * Define the relationships between this model and other models.
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function views()
    {
        return $this->morphMany(PageView::class, 'viewable');
    }

    public function references()
    {
        return $this->hasMany(PostReference::class, 'post_id');
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class, 'post_id');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function tags()
    {
        return $this->hasMany(PostTag::class, 'post_id');
    }

    public function reviews()
    {
        return $this->hasMany(PostReview::class, 'post_id');
    }

    public function postReviews()
    {
        return $this->reviews();
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
