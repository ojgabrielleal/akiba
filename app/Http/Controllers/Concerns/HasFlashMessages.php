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

        return redirect()->back()->with('flash', [
            'id' => uniqid('flash_', true),
            'type' => $action === 'error' ? 'error' : 'success',
            'icon' => $icon ?? $base['icon'],
            'message' => $final,
        ]);
    }
}
