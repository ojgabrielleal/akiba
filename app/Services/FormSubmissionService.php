<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\User;
use App\Processing\ImageProcess;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FormSubmissionService
{
    public function __construct(
        private ImageProcess $image,
    ) {}

    public function review(FormSubmission $formSubmission, User $reviewer, string $status): FormSubmission
    {
        $formSubmission->update([
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        return $formSubmission;
    }

    public function comment(FormSubmission $formSubmission, User $user, string $comment): FormSubmission
    {
        $formSubmission->comments()->create([
            'user_id' => $user->id,
            'comment' => $comment,
        ]);

        return $formSubmission;
    }

    public function destroy(FormSubmission $formSubmission): void
    {
        $formSubmission->delete();
    }

    public function store(array $data, ?UploadedFile $image = null): FormSubmission
    {
        if (($data['form_type'] ?? null) === 'event_registration' && empty($data['payload']['event_name'])) {
            $data['payload']['event_name'] = $data['payload']['event_title'] ?? null;
        }

        $imagePath = $image ? $this->image->store('listener-art-submissions', $image) : null;
        if ($imagePath) {
            $data['payload']['image'] = $imagePath;
        }

        try {
            $formSubmission = FormSubmission::create([
            'form_type' => $data['form_type'],
            'name' => $data['name'] ?? null,
            'contact' => $data['contact'] ?? null,
            'subject' => $data['subject'] ?? null,
            'payload' => $data['payload'],
            ]);
        } catch (\Throwable $exception) {
            if ($imagePath) {
                $this->image->delete($imagePath);
            }
            throw $exception;
        }

        return $formSubmission;
    }

    public function filter(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = FormSubmission::query()
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->when(
                $filters['status_order'] ?? false,
                fn (Builder $query) => $query->orderByRaw("case status when 'pending' then 0 when 'approved' then 1 else 2 end")
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
