<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
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

    protected $withCount = [
        'views',
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
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeMine($query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function scopeFeatured($query)
    {
        return $query->orderByDesc('views_count');
    }

    public function scopePost($query)
    {
        return $query->where('module', 'post');
    }

    public function scopeReview($query)
    {
        return $query->where('module', 'review');
    }

    public function scopeEvent($query)
    {
        return $query->where('module', 'event');
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
