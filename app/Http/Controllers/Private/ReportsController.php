<?php

namespace App\Http\Controllers\Private;

use App\Services\AudienceService;
use App\Services\OnairService;
use App\Services\PollService;
use App\Services\PostService;
use App\Services\SongRequestService;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFlashMessages;

use App\Http\Requests\RadioStation\StoreRadioStationRequest;
use App\Http\Requests\RadioStation\UpdateRadioStationRequest;
use App\Http\Resources\AudienceResource;
use App\Http\Resources\Onair\OnairResource;
use App\Http\Resources\SongRequestResource;
use App\Http\Resources\User\UserResource;

use App\Models\Onair;
use App\Models\Poll;
use App\Models\Post;
use App\Models\RadioStation;
use App\Models\SongRequest;
use App\Models\User;

use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    use HasFlashMessages;

    private $render = 'private/Reports';

    public function __construct(
        private AudienceService $audienceFilter,
        private OnairService $onairFilter,
        private PollService $pollFilter,
        private PostService $postFilter,
        private SongRequestService $songRequestFilter,
    ) {}

    private function indexAudience()
    {
        return AudienceResource::collection(
            $this->audienceFilter->filter([
                'active' => true,
                'with' => 'latestAudienceSnapshot',
                'order_by_audience' => true,
            ])
        );
    }

    private function indexAudienceHistory()
    {
        return AudienceResource::collection(
            $this->audienceFilter->history([
                'period' => $this->audiencePeriod(),
            ])
        )->format('history');
    }

    private function indexOnair()
    {
        return OnairResource::collection(
            $this->onairFilter->filter([
                'execution_modes' => ['live', 'scheduled'],
                'with' => ['host', 'program.host'],
                'paginate' => 5,
            ])
        );
    }

    private function indexPostAccess()
    {
        $type = request()->string('post_access_type')->toString();
        $period = request()->integer('post_access_period');

        return $this->postFilter->accessReport([
            'type' => in_array($type, ['recognized', 'anonymous'], true) ? $type : 'all',
            'period' => in_array($period, [15, 30], true) ? $period : null,
        ]);
    }

    private function indexInternalRanking(): array
    {
        return [
            'redator_mais_ativo' => $this->redatorMaisAtivo(),
            'locutor_mais_ativo' => $this->locutorMaisAtivo(),
            'pedidos_atendidos' => $this->pedidosAtendidos(),
            'pico_audiencia' => $this->picoAudiencia(),
            'maior_interacao' => $this->maiorInteracao(),
            'enquete_mais_votada' => $this->enqueteMaisVotada(),
        ];
    }

    private function audiencePeriod(): string
    {
        $period = request()->string('audience_period')->toString();

        return in_array($period, ['day', 'week', 'month', 'semester'], true)
            ? $period
            : 'day';
    }

    private function redatorMaisAtivo(): ?array
    {
        $ranking = Post::query()
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->where('module', 'post')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        if (! $ranking) {
            return null;
        }

        $author = User::find($ranking->user_id);

        if (! $author) {
            return null;
        }

        return [
            'usuario' => UserResource::make($author)
                ->format('summary')
                ->resolve(request()),
            'total' => (int) $ranking->total,
        ];
    }

    private function locutorMaisAtivo(): ?array
    {
        $ranking = Onair::query()
            ->leftJoin('programs', 'programs.id', '=', 'onairs.program_id')
            ->join('users', 'users.id', '=', DB::raw('COALESCE(onairs.user_id, programs.user_id)'))
            ->selectRaw('users.id as user_id, COUNT(*) as total')
            ->where('users.is_virtual', false)
            ->groupBy('users.id')
            ->orderByDesc('total')
            ->first();

        if (! $ranking) {
            return null;
        }

        $host = User::find($ranking->user_id);

        if (! $host) {
            return null;
        }

        return [
            'usuario' => UserResource::make($host)
                ->format('summary')
                ->resolve(request()),
            'total' => (int) $ranking->total,
        ];
    }

    private function pedidosAtendidos(): ?array
    {
        $ranking = SongRequest::query()
            ->selectRaw('DATE(DATE_SUB(created_at, INTERVAL 3 HOUR)) as request_date, COUNT(*) as total')
            ->where('type', 'music')
            ->where('was_reproduced', true)
            ->groupBy('request_date')
            ->orderByDesc('total')
            ->first();

        if (! $ranking) {
            return null;
        }

        return [
            'data' => date('d/m/Y', strtotime($ranking->request_date)),
            'total' => (int) $ranking->total,
        ];
    }

    private function maiorInteracao(): ?array
    {
        $post = $this->postFilter->filter([
                'user' => request()->user(),
            'module' => 'post',
            'with_count' => 'reactions',
            'order_by' => 'reactions_count',
            'limit' => 1,
            'ignore_authorization' => true,
        ])->first();

        if (! $post) {
            return null;
        }

        return [
            'uuid' => $post->uuid,
            'titulo' => $post->title,
            'capa' => $post->cover,
            'total' => $post->reactions_count,
        ];
    }

    private function picoAudiencia(): ?array
    {
        $onair = $this->onairFilter->filter([
            'with_audience_peak' => true,
            'with' => 'program',
            'order_by' => 'peak_listeners',
            'first' => true,
        ]);

        if (! $onair?->program) {
            return null;
        }

        return [
            'programa' => [
                'uuid' => $onair->program->uuid,
                'nome' => $onair->program->name,
                'imagem' => $onair->program->image,
            ],
            'total' => $onair->peak_listeners,
            'data' => $onair->peak_listeners_at
                ?->setTimezone('America/Sao_Paulo')
                ->format('d/m/Y H:i'),
        ];
    }

    private function enqueteMaisVotada(): ?array
    {
        $poll = $this->pollFilter->filter([
            'with_count' => 'votes',
            'order_by' => 'votes_count',
            'first' => true,
        ]);

        if (! $poll instanceof Poll) {
            return null;
        }

        return [
            'uuid' => $poll->uuid,
            'pergunta' => $poll->question,
            'total' => $poll->votes_count,
        ];
    }

    public function storeRadioStation(StoreRadioStationRequest $request, AudienceService $service)
    {
        $service->store($request->validated());

        return $this->flashMessage('create');
    }

    public function updateRadioStation(UpdateRadioStationRequest $request, AudienceService $service, RadioStation $radioStation)
    {
        $service->update($radioStation, $request->validated());

        return $this->flashMessage('update');
    }

    public function destroyRadioStation(AudienceService $service, RadioStation $radioStation)
    {
        $this->authorize('delete', $radioStation);

        $service->destroy($radioStation);

        return $this->flashMessage('delete');
    }

    public function showOnairSongRequests(Onair $onair)
    {
        $targetOnair = $onair->in_air
            ? Onair::acceptingSongRequests()
                ->where('program_id', $onair->program_id)
                ->latest('id')
                ->first() ?? $onair
            : $onair;

        return SongRequestResource::collection(
            $this->songRequestFilter->filter([
                'onair_id' => $targetOnair->id,
                'order_by' => 'created_at',
                'order_direction' => 'desc',
            ])
        );
    }

    public function showPostViewers(Post $post)
    {
        return response()->json($post->views()
            ->whereNotNull('actor_id')
            ->with('actor')
            ->latest('created_at')
            ->get()
            ->unique(fn ($view) => $view->actor_type . ':' . $view->actor_id)
            ->values()
            ->map(fn ($view) => [
                'name' => $view->actor?->nickname ?? $view->actor?->name ?? $view->actor?->username ?? 'Sem nome',
                'last_view' => $view->created_at->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ]));
    }

    public function render()
    {
        return Inertia::render($this->render, [
            'postAccess' => Inertia::defer(fn () => $this->indexPostAccess(), 'reports-posts'),
            'audience' => Inertia::defer(fn () => $this->indexAudience(), 'reports-audience'),
            'audienceHistory' => Inertia::defer(fn () => $this->indexAudienceHistory(), 'reports-history'),
            'onair' => Inertia::defer(fn () => $this->indexOnair(), 'reports-history'),
            'ranking_interno' => Inertia::defer(fn () => $this->indexInternalRanking(), 'reports-ranking'),
        ]);
    }
}
