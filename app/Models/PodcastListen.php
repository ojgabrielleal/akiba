<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PodcastListen extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'podcast_id',
        'listener_type',
        'listener_id',
        'listened_at',
    ];

    protected $casts = [
        'listened_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function podcast()
    {
        return $this->belongsTo(Podcast::class, 'podcast_id');
    }

    public function listener(): MorphTo
    {
        return $this->morphTo();
    }
}
