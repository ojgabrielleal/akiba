<?php

namespace App\Services;

use App\Models\Repository;
use Illuminate\Support\Facades\DB;
use App\Processing\ImageProcess;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RepositoryService
{
    public function __construct(
        private ImageProcess $image,
        private CacheService $cache,
    ) {}

    public function deactivate(Repository $repository): Repository
    {
        $repository = DB::transaction(function () use ($repository) {
            $repository->update(['is_active' => false]);

            return $repository;
        });

        $this->cache->invalidateRepositories();
        $this->cache->invalidateTrash();

        return $repository;
    }

    public function store(array $data, UploadedFile $image): Repository
    {
        $repository = DB::transaction(fn () => Repository::create([
            'name' => $data['name'],
            'url' => $data['url'],
            'image' => $this->image->store('repository', $image),
            'type' => $data['type'],
        ]));

        $this->cache->invalidateRepositories();

        return $repository;
    }

    public function update(Repository $repository, array $data, ?UploadedFile $image = null): Repository
    {
        $repository = DB::transaction(function () use ($repository, $data, $image) {
            $repository->fill([
                'name' => $data['name'] ?? $repository->name,
                'url' => $data['url'] ?? $repository->url,
                'image' => $this->image->store('repository', $image, $repository->image),
                'type' => $data['type'] ?? $repository->type,
            ]);

            if ($repository->isDirty()) {
                $repository->save();
            }

            return $repository;
        });

        $this->cache->invalidateRepositories();

        return $repository;
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = Repository::query()
            ->when(
                array_key_exists('active', $filters),
                fn (Builder $query) => $query->where('is_active', $filters['active'])
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }}
