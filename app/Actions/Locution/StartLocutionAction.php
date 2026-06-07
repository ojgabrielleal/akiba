<?php

namespace App\Actions\Locution;

use Illuminate\Support\Facades\DB;
use App\Services\External\DiscordService;
use App\Services\External\OneSignalService;

use App\Models\Onair;
use App\Models\Plan;
use App\Models\Program;
use App\Models\User;

class StartLocutionAction
{
    private DiscordService $discord;
    private OneSignalService $oneSignal;

    public function __construct(DiscordService $discord, OneSignalService $oneSignal)
    {
        $this->discord = $discord;
        $this->oneSignal = $oneSignal;
    }

    public function execute(User $user, Program $program, array $data): void
    {
        DB::transaction(function () use ($user, $program, $data) {
            Onair::live()->first()->update([
                'in_air' => false,
            ]);

            $plan = Plan::where('action', 'start_program')
                ->where('status', 'running')
                ->lockForUpdate()
                ->first();

            $plan?->update([
                'status' => 'paused',
            ]);

            if($program->access_type === 'free') {
                $program->update([
                    'user_id' => $user->id,
                ]);
            }

            $program->onair()->create([
                'execution_mode' => 'live',
                'paused_plan_id' => $plan?->id,
                'phrase' => [
                    'text' => $data['phrase']['text'],
                    'icon' => $data['phrase']['icon'],
                    'decoration' => $data['phrase']['decoration'],
                    'texture' => $data['phrase']['texture'],
                ],
                'allows_song_requests' => true,
            ]);
        });

        $this->discord->sendStreamNotificationHook($user, $program);
        $this->oneSignal->sendPush(
            "{$user->nickname} está ao vivo!",
            "DJ {$user->nickname} acabou de começar o programa {$program->name}. Clique/Toque para ouvir!",
            "https://akiba.com.br",
            url($user->avatar)
        );
    }
}
