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
            $this->finishLiveOnairs();
            $this->assignFreeProgram($program, $user);
            $this->startProgram($program, $data);
        });

        if ($data['send_notification']) {
            $this->sendNotifications($user, $program);
        }
    }

    private function finishLiveOnairs(): void
    {
        Onair::live()->update([
            'in_air' => false,
        ]);
    }

    private function assignFreeProgram(Program $program, User $user): void
    {
        if ($program->access_type !== 'free') {
            return;
        }

        $program->update([
            'user_id' => $user->id,
        ]);
    }

    private function startProgram(Program $program, array $data): void
    {
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
    }

    private function sendNotifications(User $user, Program $program): void
    {
        $genderArticle = $user->gender === 'male' ? 'O' : 'A';

        $this->discord->sendStreamNotificationHook($user, $program);
        $this->oneSignal->sendPush(
            "{$genderArticle} DJ {$user->nickname} esta ao vivo na Akiba!",
            "O programa {$program->name} acabou de comecar! Cola com a gente!",
            'https://akiba.com.br',
        );
    }
}
