<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anime extends Model
{
    use HasFactory, HasUuids;

    public const DISCOGRAPHY_UNKNOWN = 'unknown';
    public const DISCOGRAPHY_PARTIAL = 'partial';
    public const DISCOGRAPHY_COMPLETE = 'complete';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'image',
        'discography_status',
        'discography_checked_at',
    ];

    protected $casts = [
        'discography_checked_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    #[Scope]
    protected function withCompleteDiscography(Builder $query): void
    {
        $query->where('discography_status', self::DISCOGRAPHY_COMPLETE);
    }

    public function musics(): HasMany
    {
        return $this->hasMany(Music::class, 'anime_id');
    }
}
