<?php

use App\Actions\OAuthAccount\Providers\DiscordOAuthAccountAction;
use App\Services\External\OAuthAccount\DiscordOAuthAccountService;

return [
    'providers' => [
        'discord' => [
            'service' => DiscordOAuthAccountService::class,
            'action' => DiscordOAuthAccountAction::class,
        ],
    ],
];
