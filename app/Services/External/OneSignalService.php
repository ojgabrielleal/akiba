<?php

namespace App\Services\External;

use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Lepresk\LaravelOnesignal\Facades\OneSignal;
use Lepresk\LaravelOnesignal\PushMessage;

class OneSignalService
{
    public function startLocution(User $user, Program $program): void
    {
        if (blank(config('onesignal.app_id')) || blank(config('onesignal.rest_api_key'))) {
            Log::warning('OneSignal notification skipped: missing configuration');

            return;
        }

        try {
            $avatarUrl = $user->avatar ? url($user->avatar) : url('/img/pwa/icon.jpg');

            $message = (new PushMessage())
                ->withName('locution_started')
                ->withTitle("{$program->name} no ar!")
                ->withBody("DJ {$user->nickname} acabou de entrar ao vivo na Akiba. Clique ou toque para ouvir agora!")
                ->withImage($avatarUrl)
                ->addButton('listen', 'Ouvir agora', url('/'))
                ->setData([
                    'type' => 'locution_started',
                    'program_uuid' => $program->uuid,
                    'user_uuid' => $user->uuid,
                    'url' => url('/'),
                    'image' => $avatarUrl,
                ])
                ->toSubscribedSegment();

            $response = OneSignal::send($message);

            if (! $response->isSuccessful()) {
                Log::warning('OneSignal notification was not sent', [
                    'status' => $response->getStatusCode(),
                    'errors' => $response->getErrors(),
                    'response' => $response->getRawResponse(),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error('OneSignal notification error: ' . $exception->getMessage());
        }
    }
}
