<?php

namespace App\Support;

class EasterEggs
{
    public static function all(): array
    {
        return [
            'konami' => [
                'key' => 'konami',
                'name' => 'Konami Code',
                'type' => 'keyboard_sequence',
                'sequence' => ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'KeyB', 'KeyA'],
            ],
            'doom_iddqd' => [
                'key' => 'doom_iddqd',
                'name' => 'DOOM - God Mode',
                'type' => 'keyboard_sequence',
                'sequence' => ['KeyI', 'KeyD', 'KeyD', 'KeyQ', 'KeyD'],
            ],
            'mortal_kombat_abacabb' => [
                'key' => 'mortal_kombat_abacabb',
                'name' => 'Mortal Kombat - Blood Code',
                'type' => 'keyboard_sequence',
                'sequence' => ['KeyA', 'KeyB', 'KeyA', 'KeyC', 'KeyA', 'KeyB', 'KeyB'],
            ],
            'sonic_2_level_select' => [
                'key' => 'sonic_2_level_select',
                'name' => 'Sonic 2 - Level Select',
                'type' => 'keyboard_sequence',
                'sequence' => ['ArrowUp', 'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight'],
            ],
            'super_mario_continue' => [
                'key' => 'super_mario_continue',
                'name' => 'Super Mario Bros. - Continue',
                'type' => 'keyboard_sequence',
                'sequence' => ['KeyA', 'KeyB', 'KeyB', 'KeyA'],
            ],
            'page_404' => [
                'key' => 'page_404',
                'name' => 'Página 404',
                'type' => 'page',
                'sequence' => null,
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::all());
    }
}
