<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OAuth extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'oauth';

    protected $fillable = [
        'uuid',
        'provider',
        'account_token_hash',
    ];

    protected $hidden = [
        'account_token_hash',
    ];

    protected $casts = [
        'provider' => 'array',
    ];

    /**
     * Determine the columns that should receive a unique identifier.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Define the relationships between this model and other models.
     */
    public function pollVotes()
    {
        return $this->hasMany(PollVote::class, 'oauth_id');
    }
}
