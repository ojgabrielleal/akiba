<?php

use App\Actions\OAuth\DiscordOAuthAction;
use App\Services\External\OAuth\DiscordOAuthService;

return [
    'providers' => [
        'discord' => [
            'service' => DiscordOAuthService::class,
            'action' => DiscordOAuthAction::class,
        ],
    ],
];
