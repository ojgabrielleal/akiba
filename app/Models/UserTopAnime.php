<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTopAnime extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'user_id',
        'position',
        'anime_theme_list_id',
        'slug',
        'name',
        'image',
        'metadata',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected $casts = [
        'position' => 'integer',
        'metadata' => 'array',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
