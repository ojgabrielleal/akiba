<?php

namespace App\Http\Controllers\Concerns;

trait HasFlashMessages
{
    public function flashMessage(string $action, ?string $message = null, ?string $icon = null)
    {
        $messages = [
            'save' => [
                'icon' => '🥳',
                'message' => 'Salvo! Que feito, hein?',
            ],
            'update' => [
                'icon' => '🫡',
                'message' => 'Atualizado! Ficou impecável.',
            ],
            'delete' => [
                'icon' => '☠️',
                'message' => 'Removido! Já tava fazendo hora extra.',
            ],
            'deactivate' => [
                'icon' => '😴',
                'message' => 'Desativado! Bora descansar também.',
            ],
            'activate' => [
                'icon' => '⚡',
                'message' => 'Ativado! Voltou com energia.',
            ],
            'complete' => [
                'icon' => '🎯',
                'message' => 'Concluído! Finalmente, né.',
            ],
            'start' => [
                'icon' => '🚀',
                'message' => 'Iniciado! Agora vai.',
            ],
            'finish' => [
                'icon' => '🎊',
                'message' => 'Finalizado! Missão cumprida.',
            ],
            'dependencies' => [
                'icon' => '⛓️',
                'message' => 'Tire os vínculos antes! Senão dá ruim.',
            ],
            'error' => [
                'icon' => '❌',
                'message' => 'Algo deu errado! Acontece nas melhores famílias.',
            ],
        ];

        $base = $messages[$action] ?? $messages['save'];
        $final = $message ?? $base['message'];

        session()->flash('flash', [
            'id' => uniqid('flash_', true),
            'type' => $action === 'error' ? 'error' : 'success',
            'icon' => $icon ?? $base['icon'],
            'message' => $final,
        ]);

        if (method_exists($this, 'render')) {
            return app()->call([$this, 'render'], request()->route()?->parameters() ?? []);
        }

        if (method_exists($this, 'show')) {
            return app()->call([$this, 'show'], request()->route()?->parameters() ?? []);
        }

        return response()->noContent();
    }
}
