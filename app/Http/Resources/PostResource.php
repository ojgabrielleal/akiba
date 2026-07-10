<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\HasFormats;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    use HasFormats;

    public function toArray(Request $request): array
    {
        $postData = [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'image' => $this->image,
            'cover' => $this->cover,
            'author' => UserResource::make($this->author)->format('summary'),
            'references' => ReferenceResource::collection($this->references),
            'tags' => TagResource::collection($this->tags),
            'module' => $this->module,
        ];

        if ($this->format === 'summary') {
            return [
                'uuid' => $this->uuid,
                'slug' => $this->slug,
                'status' => $this->status,
                'title' => $this->title,
                'module' => $this->module,
                'author' => UserResource::make($this->author)->format('summary'),
                'views' => $this->views_count,
            ];
        }

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
                'reactions' => ReactionResource::collection($this->reactions),
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

    private function reviewCurrentUser(Request $request): array
    {
        $user = $request->user();
        $opinion = $this->reviews->first(
            fn ($opinion) => $opinion->user_id === $user->id
        );

        if ($opinion) {
            return PostReviewResource::make($opinion)->resolve();
        }

        return $this->reviewGhostOpinion($user);
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
                'uuid' => null,
                'status' => 'not_created',
                'content' => null,
                'author' => UserResource::make($user)->format('summary'),
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
