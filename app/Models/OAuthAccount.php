<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OAuthAccount extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'oauth_accounts';

    protected $fillable = [
        'uuid',
        'provider',
        'provider_user_id',
        'username',
        'nickname',
        'address',
        'avatar',
        'birth_date',
        'profile_completed_at',
        'account_token_hash',
    ];

    protected $hidden = [
        'account_token_hash',
    ];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
        'profile_completed_at' => 'datetime',
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
        return $this->morphMany(PollVote::class, 'voter');
    }

    public function songRequests()
    {
        return $this->morphMany(SongRequest::class, 'requester');
    }

    public function listenerMonths()
    {
        return $this->hasMany(ListenerMonth::class, 'oauth_account_id');
    }

    public function postReactions()
    {
        return $this->morphMany(PostReaction::class, 'reactor');
    }

    public function postLikes()
    {
        return $this->morphMany(PostLike::class, 'liker');
    }

    public function postComments()
    {
        return $this->morphMany(PostComment::class, 'author');
    }
}
