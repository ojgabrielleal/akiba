<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'roles';

    protected $fillable = [
        'uuid',
        'label',
        'name',
        'description',
        'weight'
    ];

    protected $casts = [
        'weight' => 'integer'
    ];

    protected function label(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => [
                'label' => $value,
                'name' => Str::slug($value),
            ],
        );
    }

    /**
     * Determine the columns that should receive a unique identifier.
     *
     * This method specifies that the 'uuid' column should be automatically 
     * generated as a sortable, unique identifier when the model is created.
     *
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Define the relationships between this model and other models.
     *
     * Use these methods to access related data via Eloquent relationships
     * (hasOne, hasMany, belongsTo, belongsToMany, etc.).
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permissions_pivot', 'role_id', 'permission_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'roles_pivot', 'role_id', 'user_id');
    }
}
