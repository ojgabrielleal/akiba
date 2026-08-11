<?php

namespace App\Services;

use App\Models\ListenerGallery;
use App\Processing\ImageProcess;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ListenerGalleryService
{
    public function __construct(
        private ImageProcess $image,
    ) {}

    public function destroy(ListenerGallery $listenerGallery): void
    {
        DB::transaction(function () use ($listenerGallery) {
            $this->image->delete($listenerGallery->image);
            $listenerGallery->delete();
        });
    }

    public function store(User $user, array $data, UploadedFile $image): ListenerGallery
    {
        return DB::transaction(fn () => ListenerGallery::create([
            'user_id' => $user->id,
            'image' => $this->image->store('listener-gallery', $image),
            'caption' => $data['caption'] ?? null,
            'listener_name' => $data['listener_name'] ?? null,
        ]));
    }

    public function update(ListenerGallery $listenerGallery, array $data, ?UploadedFile $image = null): ListenerGallery
    {
        return DB::transaction(function () use ($listenerGallery, $data, $image) {
            $listenerGallery->fill([
                'image' => $this->image->store('listener-gallery', $image, $listenerGallery->image),
                'caption' => $data['caption'] ?? null,
                'listener_name' => $data['listener_name'] ?? null,
            ]);

            if ($listenerGallery->isDirty()) {
                $listenerGallery->save();
            }

            return $listenerGallery;
        });
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = ListenerGallery::query()
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            )
            ->when(
                $filters['limit'] ?? null,
                fn (Builder $query, int $limit) => $query->limit($limit),
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    }}
