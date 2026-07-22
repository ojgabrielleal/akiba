<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioStation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'logo',
        'website',
        'endpoint',
        'listeners_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function audienceSnapshots()
    {
        return $this->hasMany(RadioAudienceSnapshot::class);
    }

    public function latestAudienceSnapshot()
    {
        return $this->hasOne(RadioAudienceSnapshot::class)->latestOfMany();
    }
}
