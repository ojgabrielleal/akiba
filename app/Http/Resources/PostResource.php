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
            'status' => $this->status,
            'title' => $this->title,
            'image' => $this->image,
            'cover' => $this->cover,
            'author' => UserResource::make($this->author)->format('summary'),
            'references' => ReferenceResource::collection($this->references),
            'tags' => TagResource::collection($this->tags),
            'module' => $this->module(),
            'views' => $this->views_count,
        ];

        if ($this->format === 'summary') {
            return [
                'uuid' => $this->uuid,
                'slug' => $this->slug,
                'status' => $this->status,
                'title' => $this->title,
                'module' => $this->module(),
                'author' => UserResource::make($this->author)->format('summary'),
            ];
        }

        return array_merge(
            $postData,
            $this->post(),
            $this->event(),
            $this->review($request),
        );
    }

    private function module(): string
    {
        if ($this->review) {
            return 'review';
        }

        if ($this->event) {
            return 'event';
        }

        return 'post';
    }

    public function post(): array 
    {
        if(!$this->review && !$this->event){
            return [
                'content' => $this->content,
                'reactions' => ReactionResource::collection($this->reactions)
            ];
        }

        return [];
    }

    public function event(): array
    {
        if($this->event){
            return [
                'content'=> $this->content,
                'dates' => $this->event->dates,
                'address' => $this->event->address,
            ];
        }

        return [];
    }

    public function review(Request $request): array
    {
        if($this->review){
            return [
                'year_of_release' => $this->review->year_of_release,
                'sinopse' => $this->review->sinopse,
                'opinions' => $this->reviewListOpinions($request),
                'review' => $this->reviewCurrentUserOpinion($request),
            ];
        }

        return [];
    }

    private function reviewCurrentUserOpinion(Request $request): array
    {
        $user = $request->user();
        $opinion = $this->review->opinions->first(
            fn ($opinion) => $opinion->user_id === $user->id
        );

        if ($opinion) {
            return OpinionResource::make($opinion)->resolve();
        }

        return $this->reviewGhostOpinion($user);
    }

    private function reviewListOpinions(Request $request): array
    {
        if (!$request->user()->hasPermission('post.review.opinion.list')) {
            return [];
        }

        $user = $request->user();
        $opinions = OpinionResource::collection($this->review->opinions)->resolve();

        $userOpinion = collect($opinions)->first(
            fn ($opinion) => $opinion['author']['uuid'] === $user->uuid
        );

        if (!$userOpinion) {
            return [
                $this->reviewGhostOpinion($user),
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

    private function reviewGhostOpinion($user): array
    {
        return [
            'uuid' => null,
            'status' => 'not_created',
            'content' => null,
            'author' => UserResource::make($user)->format('summary'),
        ];
    }
}
