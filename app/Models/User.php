<?php

namespace App\Models;

use App\Models\Concerns\HasPermissions;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPermissions, HasUuids, Notifiable;


    protected $fillable = [
        'uuid',
        'is_active',
        'is_virtual',
        'slug',
        'username',
        'password',
        'name',
        'nickname',
        'gender',
        'avatar',
        'birth_date',
        'city',
        'state',
        'country',
        'bibliography',
        'account_token_hash',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'account_token_hash',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_virtual' => 'boolean',
        'birth_date' => 'date:Y-m-d',
    ];

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value) ? Hash::make($value) : null,
        );
    }

    protected function nickname(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => [
                'nickname' => $value,
                'slug' => Str::slug($value),
            ],
        );
    }

    /**
     * Determine the columns that should receive a unique identifier.
     *
     * This method specifies that the 'uuid' column should be automatically
     * generated as a sortable, unique identifier when the model is created.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Query scopes for this model.
     *
     * These methods define reusable query filters that can be
     * applied to Eloquent queries (e.g., active()).
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Define the relationships between this model and other models.
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function favorites()
    {
        return $this->hasMany(UserFavorite::class, 'user_id');
    }

    public function topAnimes()
    {
        return $this->hasMany(UserTopAnime::class, 'user_id')->orderBy('position');
    }

    public function socials()
    {
        return $this->hasMany(UserSocial::class, 'user_id');
    }

    public function preferences()
    {
        return $this->hasMany(UserPreference::class, 'user_id');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_pivot', 'user_id', 'role_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'user_id');
    }

    public function calendar()
    {
        return $this->hasMany(Calendar::class, 'user_id');
    }

    public function events()
    {
        return $this->posts()->forModule('event');
    }

    public function programs()
    {
        return $this->hasMany(Program::class, 'user_id');
    }

    public function programSchedules()
    {
        return $this->hasMany(ProgramSchedule::class, 'user_id');
    }

    public function podcasts()
    {
        return $this->hasMany(Podcast::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function polls()
    {
        return $this->hasMany(Poll::class, 'user_id');
    }

    public function opinions()
    {
        return $this->hasMany(PostReview::class, 'user_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id');
    }

    public function pollVotes()
    {
        return $this->morphMany(PollVote::class, 'voter');
    }

    public function songRequests()
    {
        return $this->morphMany(SongRequest::class, 'requester');
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
