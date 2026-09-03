<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private CacheService $cache,
    ) {}

    public function deactivate(User $user): User
    {
        $user = DB::transaction(function () use ($user) {
            $user->update(['is_active' => false]);

            return $user;
        });

        $this->cache->invalidateUsers();
        $this->cache->invalidateTrash();

        return $user;
    }

    public function store(array $data): User
    {
        $user = DB::transaction(function () use ($data) {
            $user = $this->storeStoreUser($data);
            $this->storeAttachRoles($user, $data['roles'] ?? []);
            $this->storeStoreDefaults($user);

            return $user;
        });

        $this->cache->invalidateUsers();
        $this->cache->invalidateRoles();

        return $user;
    }

    private function storeStoreUser(array $data): User
    {
        return User::create([
            'username' => $data['username'],
            'password' => $data['password'],
            'name' => $data['name'],
            'avatar' => "/img/placeholders/avatar-{$data['gender']}.webp",
            'nickname' => $data['nickname'],
            'gender' => $data['gender'],
            'is_virtual' => $data['is_virtual'],
        ]);
    }

    private function storeAttachRoles(User $user, array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)
            ->pluck('id')
            ->toArray();

        $user->roles()->attach($roles);
    }

    private function storeStoreDefaults(User $user): void
    {
        $user->socials()->createMany($this->storeDefaultSocials());
        $user->preferences()->createMany($this->storeDefaultPreferences());
        $user->favorites()->createMany($this->storeDefaultFavorites());
    }

    private function storeDefaultSocials(): array
    {
        return [
            ['name' => 'Twitter', 'url' => null],
            ['name' => 'Facebook', 'url' => null],
            ['name' => 'Instagram', 'url' => null],
            ['name' => 'Youtube', 'url' => null],
            ['name' => 'Discord', 'url' => null],
        ];
    }

    private function storeDefaultPreferences(): array
    {
        return [
            ['is_like' => true, 'content' => null],
            ['is_like' => true, 'content' => null],
            ['is_like' => true, 'content' => null],
            ['is_like' => false, 'content' => null],
            ['is_like' => false, 'content' => null],
            ['is_like' => false, 'content' => null],
        ];
    }

    private function storeDefaultFavorites(): array
    {
        return [
            ['name' => null, 'image' => null],
            ['name' => null, 'image' => null],
            ['name' => null, 'image' => null],
        ];
    }

    public function updateUserAccess(User $user, array $data): User
    {
        $user = DB::transaction(function () use ($user, $data) {
            $this->updateUserAccessSyncRoles($user, $data);
            $this->updateUserAccessUpdatePassword($user, $data);

            return $user;
        });

        $this->cache->invalidateUsers();
        $this->cache->invalidateRoles();

        return $user;
    }

    private function updateUserAccessSyncRoles(User $user, array $data): void
    {
        if (! array_key_exists('roles', $data)) {
            return;
        }

        $roles = Role::whereIn('name', $data['roles'] ?? [])
            ->pluck('id')
            ->toArray();

        $user->roles()->sync($roles);
    }

    private function updateUserAccessUpdatePassword(User $user, array $data): void
    {
        if (empty($data['password'])) {
            return;
        }

        $user->update([
            'password' => $data['password'],
        ]);
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = User::query()
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->when(
                array_key_exists('is_virtual', $filters),
                fn (Builder $query) => $query->where('is_virtual', $filters['is_virtual'])
            )
            ->when(
                $filters['has_roles'] ?? false,
                fn (Builder $query) => $query->whereHas('roles')
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search) {
                    $term = '%'.trim($search).'%';

                    $query->whereLike('name', $term)
                        ->orWhereLike('nickname', $term);
                })
            )
            ->when(
                $filters['virtual_last'] ?? false,
                fn (Builder $query) => $query->orderBy('is_virtual', 'asc')
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            )
            ->when(
                $filters['limit'] ?? null,
                fn (Builder $query, int $limit) => $query->limit($limit)
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }}
