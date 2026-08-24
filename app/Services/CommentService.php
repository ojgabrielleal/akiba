<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function filter(Model $commentable, bool $canModerate = false, int $perPage = 10): LengthAwarePaginator
    {
        return $commentable->comments()
            ->whereNull('parent_id')
            ->when(! $canModerate, fn (Builder $query) => $query->visible())
            ->with([
                'author',
                'replies' => fn ($query) => $query->when(! $canModerate, fn (Builder $query) => $query->visible()),
                'replies.author',
            ])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function store(Model $commentable, Model $author, array $data): Comment
    {
        return DB::transaction(function () use ($commentable, $author, $data) {
            $comment = $commentable->comments()->make([
                'parent_id' => $data['parent_id'] ?? null,
                'comment' => $data['comment'],
            ]);

            $comment->author()->associate($author);
            $comment->save();

            return $comment;
        });
    }

    public function update(Comment $comment, array $data): Comment
    {
        return DB::transaction(function () use ($comment, $data) {
            $comment->update([
                'comment' => $data['comment'],
            ]);

            return $comment;
        });
    }

    public function delete(Comment $comment): void
    {
        DB::transaction(fn () => $comment->delete());
    }

    public function approve(Comment $comment, User $moderator, ?string $reason = null): Comment
    {
        return $this->moderate($comment, $moderator, Comment::STATUS_VISIBLE, $reason);
    }

    public function hide(Comment $comment, User $moderator, ?string $reason = null): Comment
    {
        return $this->moderate($comment, $moderator, Comment::STATUS_HIDDEN, $reason);
    }

    public function restore(Comment $comment, User $moderator, ?string $reason = null): Comment
    {
        return $this->moderate($comment, $moderator, Comment::STATUS_VISIBLE, $reason);
    }

    private function moderate(Comment $comment, User $moderator, string $status, ?string $reason = null): Comment
    {
        return DB::transaction(function () use ($comment, $moderator, $status, $reason) {
            $comment->update([
                'status' => $status,
                'moderated_by' => $moderator->id,
                'moderated_at' => now(),
                'moderation_reason' => $reason,
            ]);

            return $comment;
        });
    }
}
