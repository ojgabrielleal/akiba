<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\Concerns\HasFormats;
use App\Http\Resources\User\UserResource;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    use HasFormats;

    public function toArray(Request $request): array
    {
        if ($this->format === 'featured') {
            return [
                'model' => $this->model,
                'uuid' => $this->uuid,
                'slug' => $this->slug,
                'title' => $this->title,
                'image' => $this->image,
                'cover' => $this->cover,
                'views' => $this->views_count,
            ];
        }

        if ($this->format === 'home-list') {
            return [
                'model' => $this->model,
                'uuid' => $this->uuid,
                'slug' => $this->slug,
                'title' => $this->title,
                'image' => $this->image,
                'cover' => $this->cover,
                'metadata' => $this->metadata,
                'tags' => PostTagResource::collection($this->tags),
            ];
        }

        $postData = [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'image' => $this->image,
            'cover' => $this->cover,
            'author' => UserResource::make($this->author)->format('summary'),
            'references' => PostReferenceResource::collection($this->references),
            'tags' => PostTagResource::collection($this->tags),
            'module' => $this->module,
        ];

        return array_merge(
            $postData,
            $this->post(),
            $this->event(),
            $this->review($request),
        );
    }

    public function post(): array 
    {
        if($this->module === 'post'){
            return [
                'status' => $this->status,
                'content' => $this->content,
                'reactions' => PostReactionResource::collection($this->reactions),
            ];
        }

        return [];
    }

    public function event(): array 
    {
        if($this->module === 'event'){
            return [
                'status' => $this->status,
                'content' => $this->content,
                'metadata' => $this->metadata,
            ];
        }

        return [];
    }

    public function review(Request $request): array
    {
        if($this->module === 'review'){
            return [
                'reviews' => $this->listReviews($request),
                'review' => $this->reviewCurrentUser($request),
                'metadata' => $this->metadata,
            ];
        }

        return [];
    }
    
    private function reviewGhostUser(User $user): array
    {
        return [
            'uuid' => null,
            'status' => 'not_created',
            'content' => null,
            'author' => UserResource::make($user)->format('summary'),
        ];
    }

    private function reviewCurrentUser(Request $request): array
    {
        $user = $request->user();
        $opinion = $this->reviews->first(
            fn ($opinion) => $opinion->user_id === $user->id
        );

        if ($opinion) {
            return PostReviewResource::make($opinion)->resolve();
        }

        return $this->reviewGhostUser($user);
    }

    private function listReviews(Request $request): array
    {
        if (!$request->user()->hasPermission('post.review.opinion.list')) {
            return [];
        }

        $user = $request->user();
        $opinions = PostReviewResource::collection($this->reviews)->resolve();

        $userOpinion = collect($opinions)->first(
            fn ($opinion) => $opinion['author']['uuid'] === $user->uuid
        );

        if (!$userOpinion) {
            return [
                $this->reviewGhostUser($user),
                ...$opinions,
            ];
        }

        return [
            $userOpinion,
            ...collect($opinions)
                ->reject(fn ($opinion) => $opinion['author']['uuid'] === $user->uuid)
                ->values()
                ->all(),
        ];
    }
}
