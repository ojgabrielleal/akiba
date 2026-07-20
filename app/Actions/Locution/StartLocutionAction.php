<?php

namespace App\Actions\Locution;

use App\Models\Onair;
use App\Models\Program;
use App\Models\User;

use App\Services\External\DiscordWebhookService;
use App\Services\External\OneSignalService;

use Illuminate\Support\Facades\DB;

class StartLocutionAction
{
    private DiscordWebhookService $discord;
    private OneSignalService $oneSignal;

    public function __construct(DiscordWebhookService $discord, OneSignalService $oneSignal)
    {
        $this->discord = $discord;
        $this->oneSignal = $oneSignal;
    }

    public function execute(User $user, Program $program, array $data): void
    {
        DB::transaction(function () use ($user, $program, $data) {
            Onair::live()->update([
                'in_air' => false,
            ]);

            if ($program->access_type === 'free') {
                $program->update([
                    'user_id' => $user->id,
                ]);
            }

            $program->onair()->create([
                'execution_mode' => 'live',
                'phrase' => [
                    'text' => $data['phrase']['text'],
                    'icon' => $data['phrase']['icon'],
                    'decoration' => $data['phrase']['decoration'],
                    'texture' => $data['phrase']['texture'],
                ],
                'allows_song_requests' => true,
            ]);
        });

        $genderArticle = $user->gender === 'male' ? 'O' : 'A';

        if ($data['send_notification']) {
            $this->discord->sendStreamNotificationHook($user, $program);
            $this->oneSignal->sendPush(
                "{$genderArticle} DJ {$user->nickname} esta ao vivo na Akiba!",
                "O programa {$program->name} acabou de comecar! Cola com a gente!",
                'https://akiba.com.br',
            );
        }
    }
}
