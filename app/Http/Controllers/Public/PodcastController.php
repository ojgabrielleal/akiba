<?php

namespace App\Http\Controllers\Public;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Podcast;
use App\Models\Comment;
use App\Services\PodcastService;
use App\Services\CommentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PodcastResource;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Support\AuthenticatedMember;

class PodcastController extends Controller
{
    public function __construct(
        private PodcastService $podcastFilter,
        private CommentService $commentFilter,
    ) {}

    public function render(Request $request): Response
    {
        $sort = $request->query('sort', 'lancamento');
        $sort = in_array($sort, ['lancamento', 'melhor-avaliados'], true) ? $sort : 'lancamento';

        return Inertia::render('public/Podcasts', [
            'podcasts' => PodcastResource::collection(
                $this->podcastFilter->filter([
                    'active' => true,
                    'with' => ['author'],
                    'with_count' => ['views'],
                    'order_by' => $sort === 'melhor-avaliados' ? 'views_count' : 'created_at',
                    'paginate' => 5,
                ])
            ),
            'activeSort' => $sort,
        ]);
    }

    public function read(Podcast $podcast): Response
    {
        abort_unless($podcast->is_active, 404);
        $canModerate = request()->user()?->can('viewAny', Comment::class) ?? false;

        return Inertia::render('public/ReadPodcast', [
            'podcast' => new PodcastResource($podcast->load('author')),
            'comments' => CommentResource::collection($this->commentFilter->filter($podcast, $canModerate)),
            'relatedPodcasts' => PodcastResource::collection(
                $this->podcastFilter->filter([
                    'active' => true,
                    'except' => $podcast,
                    'limit' => 2,
                ])
            ),
        ]);
    }

    public function storeComment(StoreCommentRequest $request, CommentService $service, Podcast $podcast): RedirectResponse
    {
        abort_unless($podcast->is_active, 404);

        $data = $request->validated();

        if ($parentUuid = $data['parent_uuid'] ?? null) {
            $data['parent_id'] = $podcast->comments()
                ->whereNull('parent_id')
                ->where('uuid', $parentUuid)
                ->value('id');

            abort_unless($data['parent_id'], 404);
        }

        unset($data['parent_uuid']);

        $service->store($podcast, AuthenticatedMember::fromRequest($request), $data);

        return back(303);
    }

    public function updateComment(StoreCommentRequest $request, CommentService $service, Podcast $podcast, Comment $comment): RedirectResponse
    {
        $this->ensurePodcastComment($podcast, $comment);
        $this->ensureCommentAuthor($comment);

        $service->update($comment, $request->safe()->only('comment'));

        return back(303);
    }

    public function deleteComment(Podcast $podcast, Comment $comment, CommentService $service): RedirectResponse
    {
        $this->ensurePodcastComment($podcast, $comment);
        $this->ensureCommentAuthor($comment);

        $service->delete($comment);

        return back(303);
    }

    public function approveComment(Podcast $podcast, Comment $comment, CommentService $service): RedirectResponse
    {
        $this->ensurePodcastComment($podcast, $comment);
        $this->authorize('approve', $comment);

        $service->approve($comment, request()->user());

        return back(303);
    }

    public function hideComment(Podcast $podcast, Comment $comment, CommentService $service): RedirectResponse
    {
        $this->ensurePodcastComment($podcast, $comment);
        $this->authorize('hide', $comment);

        $service->hide($comment, request()->user());

        return back(303);
    }

    public function restoreComment(Podcast $podcast, Comment $comment, CommentService $service): RedirectResponse
    {
        $this->ensurePodcastComment($podcast, $comment);
        $this->authorize('restore', $comment);

        $service->restore($comment, request()->user());

        return back(303);
    }

    public function destroyComment(Podcast $podcast, Comment $comment, CommentService $service): RedirectResponse
    {
        $this->ensurePodcastComment($podcast, $comment);
        $this->authorize('delete', $comment);

        $service->delete($comment);

        return back(303);
    }

    private function ensurePodcastComment(Podcast $podcast, Comment $comment): void
    {
        abort_unless(
            $comment->commentable_type === $podcast->getMorphClass()
                && (int) $comment->commentable_id === (int) $podcast->id,
            404
        );
    }

    private function ensureCommentAuthor(Comment $comment): void
    {
        $author = AuthenticatedMember::fromRequest(request());

        abort_unless(
            $author
                && $comment->author_type === $author->getMorphClass()
                && (int) $comment->author_id === (int) $author->getKey(),
            403
        );
    }
}
