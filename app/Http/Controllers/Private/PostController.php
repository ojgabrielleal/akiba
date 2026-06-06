<?php

namespace App\Http\Controllers\Private;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

use App\Http\Controllers\Concerns\HasFlashMessages;
use App\Http\Controllers\Concerns\ResolvesUserLogged;

use App\Models\Post;

use App\Http\Resources\PostResource;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\UpdatePostAction;

use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;

class PostController extends Controller
{
    use HasFlashMessages, ResolvesUserLogged;

    private $render = 'private/Post';

    /*
     * ======================
     * POSTS
     * ======================
     */

    public function indexPosts()
    {
        $user = request()->user();
        
        $query = Post::active()
            ->featured()
            ->with(['author', 'event', 'review.opinions'])
            ->orderBy('created_at','desc');

        if ($user->hasPermission('post.list')) {
            return PostResource::collection($query->paginate(10))->format('summary');
        }

        if ($user->hasPermission('post.list.own')) {
            $query->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('review');
            });
        }

        return PostResource::collection($query->paginate(10))->format('summary');
    }

    public function showPost(Post $post)
    {
        if (request()->user()->cannot('view', $post)) {
            return null;
        }

        return Inertia::render($this->render, [
            'post' => new PostResource($post->load(['tags', 'references', 'author', 'review.opinions'])),
            'posts' => $this->indexPosts(),
        ]);
    }

    public function createPost(CreatePostRequest $request, CreatePostAction $createPostAction)
    {
        if ($request->user()->cannot('create', Post::class)) {
            return null;
        }

        $createPostAction->execute(
            $request->user(),
            $request->all(),
            $request->file('image'),
            $request->file('cover'),
            module: $request->input('module', 'post'),
        );

        return $this->flashMessage('save');
    }

    public function updatePost(UpdatePostRequest $request, UpdatePostAction $updatePostAction, Post $post)
    {
        if ($request->user()->cannot('update', $post)) {
            return null;
        }

        $updatePostAction->execute(
            $post,
            $request->all(),
            $request->file('image'),
            $request->file('cover'),
            module: $request->input('module', 'review'),
        );

        return $this->flashMessage('update');
    }

    public function deactivatePost(Post $post)
    {
        if (request()->user()->cannot('delete', $post)) {
            return null;
        }

        $post->update([
            'is_active' => false,
        ]);

        return $this->flashMessage('deactivate');
    }

    /*
     * ======================
     * RENDER
     * ======================
     */

    public function render()
    {
        return Inertia::render($this->render, [
            'posts' => $this->indexPosts(),
        ]);
    }
}
