<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stream' => [
        'url' => env('STREAM_URL', 'https://stm3.painelcast.com:7770/stream'),
        'metadata' => env('STREAM_METADATA', 'http://cast.radioamc.com.br/api-json/Vkc1d2FrMHdNVUpRVkRBOStS'),
    ],

    'audience' => [
        'internal_station_name' => env('AUDIENCE_INTERNAL_STATION_NAME', 'Rádio Akiba'),
        'timeout' => env('AUDIENCE_TIMEOUT', 2),
        'connect_timeout' => env('AUDIENCE_CONNECT_TIMEOUT', 1),
        'slow_response_ms' => env('AUDIENCE_SLOW_RESPONSE_MS', 1500),
    ],

    'geoip' => [
        'url' => env('GEOIP_URL', 'https://api.country.is'),
        'connect_timeout' => env('GEOIP_CONNECT_TIMEOUT', 1),
        'timeout' => env('GEOIP_TIMEOUT', 2),
        'cache_days' => env('GEOIP_CACHE_DAYS', 30),
        'failure_cache_minutes' => env('GEOIP_FAILURE_CACHE_MINUTES', 5),
        'resolve_private_ips' => env('GEOIP_RESOLVE_PRIVATE_IPS', env('APP_ENV') === 'local'),
    ],

    'discord' => [
        'publication_enabled' => env('DISCORD_PUBLICATION_ENABLED', true),
        'webhook' => env('DISCORD_LOCUTION_WEBHOOK_URL'),
        'post_webhook' => env('DISCORD_POST_WEBHOOK_URL'),
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => env('DISCORD_REDIRECT_URI'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'meta' => [
        'graph_version' => env('META_GRAPH_VERSION'),
        'instagram_user_id' => env('META_INSTAGRAM_USER_ID'),
        'access_token' => env('META_ACCESS_TOKEN'),
        'timeout' => env('META_TIMEOUT', 15),
        'connect_timeout' => env('META_CONNECT_TIMEOUT', 5),
        'container_poll_attempts' => env('META_CONTAINER_POLL_ATTEMPTS', 10),
        'container_poll_interval_ms' => env('META_CONTAINER_POLL_INTERVAL_MS', 2000),
        'instagram_enabled' => env('META_INSTAGRAM_ENABLED', false),
        'instagram_feed_enabled' => env('META_INSTAGRAM_FEED_ENABLED', true),
    ],

    'webpush' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT', env('APP_URL')),
    ],

    'news_article_images' => [
        'anime-news-network' => ['host' => 'www.animenewsnetwork.com', 'path' => '/news/', 'interval' => 2, 'image_hosts' => ['www.animenewsnetwork.com', 'cdn.animenewsnetwork.com']],
    ],

    // Official RSS/Atom feeds validated on 2026-10-16; no article scraping.
    'anime_news_sources' => [
        [
            'name' => 'Anime News Network',
            'slug' => 'anime-news-network',
            'base_url' => 'https://www.animenewsnetwork.com',
            'type' => 'rss',
            'feed_url' => 'https://www.animenewsnetwork.com/news/rss.xml',
            'language' => 'en-US',
            'priority' => 3,
        ],
        [
            'name' => 'Anime United',
            'slug' => 'anime-united',
            'base_url' => 'https://www.animeunited.com.br',
            'type' => 'wordpress',
            'posts_url' => 'https://www.animeunited.com.br/wp-json/wp/v2/posts',
            'feed_url' => 'https://www.animeunited.com.br/feed/',
            'language' => 'pt-BR',
            'priority' => 1,
        ],
        [
            'name' => 'IntoxiAnime',
            'slug' => 'intoxianime',
            'base_url' => 'https://www.intoxianime.com',
            'type' => 'wordpress',
            'posts_url' => 'https://www.intoxianime.com/wp-json/wp/v2/posts',
            'feed_url' => 'https://www.intoxianime.com/feed/',
            'language' => 'pt-BR',
            'priority' => 1,
        ],
        [
            'name' => 'AnimeNew',
            'slug' => 'animenew',
            'base_url' => 'https://animenew.com.br',
            'type' => 'wordpress',
            'posts_url' => 'https://wp.animenew.com.br/wp-json/wp/v2/posts',
            'feed_url' => 'https://wp.animenew.com.br/feed/',
            'language' => 'pt-BR',
            'priority' => 1,
        ],
        [
            'name' => 'OtakuPT',
            'slug' => 'otakupt',
            'base_url' => 'https://www.otakupt.com',
            'type' => 'wordpress',
            'posts_url' => 'https://www.otakupt.com/wp-json/wp/v2/posts',
            'feed_url' => 'https://www.otakupt.com/feed/',
            'language' => 'pt-PT',
            'priority' => 2,
        ],
        [
            'name' => 'Crunchyroll',
            'slug' => 'crunchyroll',
            'base_url' => 'https://www.crunchyroll.com',
            'type' => 'rss',
            'feed_url' => 'https://cr-news-api-service.prd.crunchyrollsvc.com/v1/pt-BR/rss',
            'language' => 'pt-BR',
            'priority' => 1,
        ],
    ],

];
