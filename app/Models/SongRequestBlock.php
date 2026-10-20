<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SongRequestBlock extends Model
{
    protected $fillable = ['requester_type', 'requester_id', 'blocked_by_id', 'module', 'reason'];

    public function requester(): MorphTo
    {
        return $this->morphTo();
    }

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by_id');
    }
}
