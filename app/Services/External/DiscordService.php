<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;

class DiscordService
{
    public function sendStreamNotificationHook($user, $program)
    {
        if (!app()->environment('production')) {
            return false;
        }

        $webhookUrl = config('services.discord.webhook');
        
        if (!$webhookUrl) return false; 
            
        $genderTitle = $user->gender === 'male' ? 'O DJ' : 'A DJ';

        $payload = [
            'content' => "@everyone Olá otakus queridos!\n\n🔴 **PROGRAMA AO VIVO rolando agora!**",
            'embeds' => [
                [
                    'title' => "{$program->name} NO AR!",
                    'description' => "🎧 {$genderTitle} **{$user->nickname}** sentou no estúdio e já está no ar!\n\nOuça agora na Akiba.\n👉 **[Clique aqui para sintonizar na Akiba!](https://akiba.com.br)**",
                    'color' => hexdec("FF6B00"), // Laranja vibrante estilo Akiba
                    'author' => [
                        'name' => "DJ {$user->nickname}",
                    ],
                    'timestamp' => now()->toIso8601String()
                ]
            ]
        ];

        Http::post($webhookUrl, $payload);

        return true;
    }
}
