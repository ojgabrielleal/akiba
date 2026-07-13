<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'poll_id',
        'poll_option_id',
        'oauth_id',
        'user_id',
    ];

    protected $hidden = [
        'poll_id',
        'poll_option_id',
        'oauth_id',
        'user_id',
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
    public function poll()
    {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    public function option()
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }

    public function oauth()
    {
        return $this->belongsTo(OAuth::class, 'oauth_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
