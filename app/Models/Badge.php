<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory, HasUuids;

    public const TYPE_FIXED = 'fixed';
    public const TYPE_STEALABLE = 'stealable';
    public const TYPE_SCHEDULED = 'scheduled';

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'image',
        'type',
        'source',
        'rule',
        'is_active',
    ];

    protected $casts = [
        'rule' => 'array',
        'is_active' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function fixed(Builder $query): void
    {
        $query->where('type', self::TYPE_FIXED);
    }

    #[Scope]
    protected function stealable(Builder $query): void
    {
        $query->where('type', self::TYPE_STEALABLE);
    }

    public function assignments()
    {
        return $this->hasMany(BadgeAssignment::class, 'badge_id');
    }

    public function activeAssignments()
    {
        return $this->hasMany(BadgeAssignment::class, 'badge_id')->active();
    }

    public function currentAssignment()
    {
        return $this->hasOne(BadgeAssignment::class, 'badge_id')->active()->latestOfMany('acquired_at');
    }
}
