<?php

use App\Actions\OAuthAccount\Providers\DiscordOAuthAccountAction;
use App\Actions\OAuthAccount\Providers\GoogleOAuthAccountAction;
use App\Services\External\OAuthAccount\DiscordOAuthAccountService;
use App\Services\External\OAuthAccount\GoogleOAuthAccountService;

return [
    'providers' => [
        'discord' => [
            'service' => DiscordOAuthAccountService::class,
            'action' => DiscordOAuthAccountAction::class,
        ],
        'google' => [
            'service' => GoogleOAuthAccountService::class,
            'action' => GoogleOAuthAccountAction::class,
        ],
    ],
];
