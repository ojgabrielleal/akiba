<?php

namespace App\Http\Middleware;

use App\Services\PageViewService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPublicPageView
{
    private const CONTENT_ROUTES = [
        'post.read',
        'review.read',
        'event.read',
        'event.read.legacy',
        'podcast.read',
    ];

    private const PAGE_NAMES = [
        'home' => 'Início',
        'auth.continue' => 'Entrar',
        'contact' => 'Contato',
        'news' => 'Notícias',
        'columns' => 'Colunas',
        'team' => 'Equipe',
        'radio' => 'Rádio',
        'media' => 'Mídias',
        'podcasts' => 'Podcasts',
        'search' => 'Busca',
    ];

    public function __construct(private PageViewService $pageViewService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $routeName = $request->route()?->getName();

        if ($request->isMethod('GET')
            && $response->isSuccessful()
            && is_string($routeName)
            && ! in_array($routeName, self::CONTENT_ROUTES, true)) {
            $this->pageViewService->storePage(
                self::PAGE_NAMES[$routeName] ?? $routeName,
                $request,
            );
        }

        return $response;
    }
}
