<?php

namespace Database\Factories\Concerns;

trait HasLocutionIcons
{
    private const LOCUTION_ICONS = [
        '/img/locution/characters/rem.webp',
        '/img/locution/characters/aladdin.webp',
        '/img/locution/characters/anime-girl-cat-ears.webp',
        '/img/locution/characters/anime-girl.webp',
        '/img/locution/characters/asuza.webp',
        '/img/locution/characters/edward.webp',
        '/img/locution/characters/mya-nee.webp',
        '/img/locution/characters/rin.webp',
        '/img/locution/characters/takashi-komuro.webp',
        '/img/locution/characters/tales-of-zestria.webp',
        '/img/locution/characters/tanjiro.webp',
    ];

    protected function fakeLocutionIcon(): string
    {
        return fake()->randomElement(self::LOCUTION_ICONS);
    }
}
