<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'notifiable_type',
        'notifiable_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
}
