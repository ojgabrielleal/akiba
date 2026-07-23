<?php

namespace App\Http\Controllers\Private\Pages;

use App\Filters\AudienceFilter;
use App\Filters\OnairFilter;
use App\Filters\PollFilter;
use App\Filters\PostFilter;
use App\Filters\SongRequestFilter;

use App\Http\Controllers\Controller;

use App\Http\Resources\AudienceResource;
use App\Http\Resources\Onair\OnairResource;
use App\Http\Resources\User\UserResource;

use App\Models\Onair;
use App\Models\Poll;
use App\Models\Post;
use App\Models\SongRequest;

use Inertia\Inertia;

class ReportsPageController extends Controller
{
    private $render = 'private/Reports';

    public function __construct(
        private AudienceFilter $audienceFilter,
        private OnairFilter $onairFilter,
        private PollFilter $pollFilter,
        private PostFilter $postFilter,
        private SongRequestFilter $songRequestFilter,
    ) {}

    public function render()
    {
        return Inertia::render($this->render, [
            'audience' => AudienceResource::collection(
                $this->audienceFilter->apply([
                    'active' => true,
                    'with' => 'latestAudienceSnapshot',
                    'order_by_audience' => true,
                ])
            ),
            'audienceHistory' => fn () => AudienceResource::collection(
                $this->audienceFilter->history($this->audiencePeriod())
            )->format('history'),
            'onair' => OnairResource::collection(
                $this->onairFilter->apply([
                    'execution_modes' => ['live', 'scheduled'],
                    'with' => ['program.host'],
                    'paginate' => 5,
                ])
            ),
            'ranking_interno' => [
                'redator_mais_ativo' => $this->redatorMaisAtivo(),
                'locutor_mais_ativo' => $this->locutorMaisAtivo(),
                'pedidos_atendidos' => $this->pedidosAtendidos(),
                'pico_audiencia' => $this->picoAudiencia(),
                'maior_interacao' => $this->maiorInteracao(),
                'enquete_mais_votada' => $this->enqueteMaisVotada(),
            ],
        ]);
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
        $posts = $this->postFilter->apply(request()->user(), [
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
        $transmissoes = $this->onairFilter->apply([
            'with' => ['program.host'],
        ]);

        $ranking = $transmissoes
            ->filter(fn (Onair $onair) => $onair->program?->host !== null)
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
        $pedidos = $this->songRequestFilter->apply();

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
        $post = $this->postFilter->apply(request()->user(), [
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
            'imagem' => $post->image,
            'total' => $post->reactions_count,
        ];
    }

    private function picoAudiencia(): ?array
    {
        $onair = $this->onairFilter->apply([
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
        $poll = $this->pollFilter->apply([
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
}
