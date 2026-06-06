<?php

namespace App\Http\Controllers\Concerns;

trait HasFlashMessages
{
    public function flashMessage(string $action, ?string $message = null, ?string $icon = null)
    {
        $messages = [
            'save' => [
                'icon' => '🥳',
                'message' => 'Salvo, querido! Que feito, hein?',
            ],
            'update' => [
                'icon' => '🫡',
                'message' => 'Atualizado! Ficou maravi..., impecável.',
            ],
            'delete' => [
                'icon' => '☠️',
                'message' => 'Apagado! Já tava fazendo hora extra',
            ],
            'deactivate' => [
                'icon' => '😴',
                'message' => 'Desativado! Bora dormir também.',
            ],
            'activate' => [
                'icon' => '🥱',
                'message' => 'Ativado! Saudades, confesso.',
            ],
            'complete' => [
                'icon' => '🎯',
                'message' => 'Completado! Finalmente, né.',
            ],
            'participate' => [
                'icon' => '🙋',
                'message' => 'Participando! Corajoso, você é!',
            ],
            'start' => [
                'icon' => '🚀',
                'message' => 'Iniciado! Se não explodir...',
            ],
            'finish' => [
                'icon' => '🎊',
                'message' => 'Finalizado! Nossa, que demora, hein?',
            ],
            'order_fulfilled' => [
                'icon' => '🎵',
                'message' => 'Vamos atender! Que vibe, hein?',
            ],
            'order_canceled' => [
                'icon' => '🚫',
                'message' => 'Vamos cancelar! Triste, acontece.',
            ],
            'dependencies' => [
                'icon' => '⛓️',
                'message' => 'Tire os vínculos antes! Se não dá ruim...',
            ],
            'error' => [
                'icon' => '❌',
                'message' => 'Algo deu errado!',
            ],
        ];

        $base = $messages[$action] ?? $messages['save'];
        $final = $message ?? $base['message'];

        return redirect()->to($this->flashRedirectUrl(), $this->flashRedirectStatus())->with('flash', [
            'id' => uniqid('flash_', true),
            'type' => $action === 'error' ? 'error' : 'success',
            'icon' => $icon ?? $base['icon'],
            'message' => $final,
        ]);
    }

    private function flashRedirectUrl(): string
    {
        $request = request();
        $previous = url()->previous();
        $fallback = $this->panelPageUrlFor($request->path());

        if ($fallback && $this->isDashboardUrl($previous) && ! $request->is('panel/dashboard*')) {
            return $fallback;
        }

        if ($previous && $previous !== $request->fullUrl()) {
            return $previous;
        }

        return $fallback ?? url()->previous();
    }

    private function flashRedirectStatus(): int
    {
        return request()->isMethod('GET') ? 302 : 303;
    }

    private function panelPageUrlFor(string $path): ?string
    {
        $routes = [
            'panel/dashboard' => 'panel.dashboard',
            'panel/post' => 'panel.post',
            'panel/locution' => 'panel.locucao',
            'panel/radio' => 'panel.radio',
            'panel/podcast' => 'panel.podcast',
            'panel/marketing' => 'panel.marketing',
            'panel/media' => 'panel.medias',
            'panel/administration' => 'panel.adms',
            'panel/logs' => 'panel.logs',
        ];

        foreach ($routes as $prefix => $route) {
            if (str_starts_with($path, $prefix)) {
                return route($route);
            }
        }

        if (str_starts_with($path, 'panel/profile/')) {
            return url($path);
        }

        return null;
    }

    private function isDashboardUrl(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        return str_starts_with(parse_url($url, PHP_URL_PATH) ?? '', '/panel/dashboard');
    }
}
