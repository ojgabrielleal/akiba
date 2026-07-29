<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'post_id',
        'oauth_account_id',
        'comment',
    ];

    protected $hidden = [
        'post_id',
        'oauth_account_id',
    ];

    /**
     * Determine the columns that should receive a unique identifier.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function oauthAccount()
    {
        return $this->belongsTo(OAuthAccount::class, 'oauth_account_id');
    }
}
