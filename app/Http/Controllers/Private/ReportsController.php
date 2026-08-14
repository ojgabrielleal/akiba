<?php

namespace App\Http\Controllers\Private;

use App\Services\AudienceService;
use App\Services\OnairService;
use App\Services\PollService;
use App\Services\PostService;
use App\Services\SongRequestService;

use App\Http\Controllers\Controller;

use App\Http\Resources\AudienceResource;
use App\Http\Resources\Onair\OnairResource;
use App\Http\Resources\User\UserResource;

use App\Models\Onair;
use App\Models\Poll;
use App\Models\Post;
use App\Models\SongRequest;

use Inertia\Inertia;

class ReportsController extends Controller
{
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
                'with' => ['program.host'],
                'paginate' => 5,
            ])
        );
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
        $posts = $this->postFilter->filter([
                'user' => request()->user(),
            'module' => 'post',
            'with' => 'author',
            'ignore_authorization' => true,
        ]);

        $ranking = $posts
            ->filter(fn (Post $post) => $post->author !== null)
            ->groupBy('user_id')
            ->sortByDesc(fn ($posts) => $posts->count())
            ->first();

        if (! $ranking) {
            return null;
        }

        return [
            'usuario' => UserResource::make($ranking->first()->author)
                ->format('summary')
                ->resolve(request()),
            'total' => $ranking->count(),
        ];
    }

    private function locutorMaisAtivo(): ?array
    {
        $transmissoes = $this->onairFilter->filter([
            'with' => ['program.host'],
        ]);

        $ranking = $transmissoes
            ->filter(fn (Onair $onair) => $onair->program?->host !== null && ! $onair->program->host->is_virtual)
            ->groupBy(fn (Onair $onair) => $onair->program->user_id)
            ->sortByDesc(fn ($onair) => $onair->count())
            ->first();

        if (! $ranking) {
            return null;
        }

        return [
            'usuario' => UserResource::make($ranking->first()->program->host)
                ->format('summary')
                ->resolve(request()),
            'total' => $ranking->count(),
        ];
    }

    private function pedidosAtendidos(): ?array
    {
        $pedidos = $this->songRequestFilter->filter();

        $ranking = $pedidos
            ->groupBy(fn (SongRequest $songRequest) => $songRequest->created_at
                ->copy()
                ->setTimezone('America/Sao_Paulo')
                ->format('Y-m-d'))
            ->sortByDesc(fn ($requests) => $requests->count())
            ->first();

        if (! $ranking) {
            return null;
        }

        return [
            'data' => $ranking->first()->created_at
                ->copy()
                ->setTimezone('America/Sao_Paulo')
                ->format('d/m/Y'),
            'total' => $ranking->count(),
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

    public function render()
    {
        return Inertia::render($this->render, [
            'audience' => $this->indexAudience(),
            'audienceHistory' => fn () => $this->indexAudienceHistory(),
            'onair' => $this->indexOnair(),
            'ranking_interno' => $this->indexInternalRanking(),
        ]);
    }
}
