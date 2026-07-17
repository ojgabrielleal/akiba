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
            'error' => [
                'icon' => '❌',
                'message' => 'Algo deu errado! Acontece nas melhores famílias.',
            ],
        ];

        $action = array_key_exists($action, $messages) ? $action : 'error';
        $base = $messages[$action];
        $final = $message ?? $base['message'];

        $flash = [
            'id' => uniqid('flash_', true),
            'type' => $action === 'error' ? 'error' : 'success',
            'icon' => $icon ?? $base['icon'],
            'message' => $final,
        ];

        return back()->with('flash', $flash);
    }
}
