<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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

    public function tags()
    {
        return $this->hasMany(PostTag::class, 'post_id');
    }

    public function reviews()
    {
        return $this->hasMany(PostReview::class, 'post_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
