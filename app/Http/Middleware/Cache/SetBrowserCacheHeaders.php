<?php

namespace App\Http\Middleware\Cache;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetBrowserCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $response;
        }

        if ($this->isVersionedAsset($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            return $response;
        }

        if ($request->is('panel') || $request->is('panel/*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
            return $response;
        }

        if ($this->expectsHtml($request, $response)) {
            $response->headers->set('Cache-Control', 'no-cache, private');
        }

        return $response;
    }

    private function expectsHtml(Request $request, Response $response): bool
    {
        $contentType = (string) $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html')
            || $request->headers->has('X-Inertia')
            || $request->acceptsHtml();
    }

    private function isVersionedAsset(Request $request): bool
    {
        return $request->is('build/assets/*')
            || $request->is('img/*')
            || $request->is('svg/*')
            || $request->is('fonts/*')
            || $request->is('favicon.ico');
    }
}
