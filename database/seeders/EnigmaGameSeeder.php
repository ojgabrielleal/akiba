<?php

namespace Database\Seeders;

use App\Models\EnigmaGame;
use App\Models\EnigmaGameInteraction;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnigmaGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::query()->firstOrFail();
        $responder = User::query()
            ->where('id', $author->id)
            ->firstOrFail();

        $questionResults = ['yes', 'no', 'banal'];

        $active = EnigmaGame::factory()
            ->active()
            ->for($author, 'author')
            ->create([
                'title' => 'Teste do corredor azul',
                'content' => '/img/placeholders/default.webp',
                'solution' => 'A chave esta no segundo verso.',
            ]);

        EnigmaGameInteraction::factory(8)
            ->question()
            ->for($active)
            ->create();

        foreach ([
            'A pista envolve uma joia?',
            'O enigma acontece durante a noite?',
            'Existe uma senha escondida na imagem?',
            'A resposta tem relação com música?',
            'O baú é apenas decoração?',
            'A cor azul é importante?',
        ] as $index => $content) {
            EnigmaGameInteraction::factory()
                ->question()
                ->answered($responder)
                ->for($active)
                ->create([
                    'content' => $content,
                    'result' => $questionResults[$index % count($questionResults)],
                ]);
        }

        EnigmaGameInteraction::factory(5)
            ->incorrect($responder)
            ->for($active)
            ->create();

        EnigmaGameInteraction::factory(3)
            ->finalAnswer()
            ->for($active)
            ->create();

        $solved = EnigmaGame::factory()
            ->draft()
            ->for($author, 'author')
            ->create([
                'title' => 'Enigma resolvido',
                'content' => '/img/placeholders/default.webp',
                'solution' => 'Akiba',
            ]);

        EnigmaGameInteraction::factory()
            ->yes($responder)
            ->for($solved)
            ->create(['content' => 'A resposta envolve a rádio?']);

        EnigmaGameInteraction::factory()
            ->no($responder)
            ->for($solved)
            ->create(['content' => 'É um personagem de cabelo vermelho?']);

        EnigmaGameInteraction::factory()
            ->banal($responder)
            ->for($solved)
            ->create(['content' => 'Tem anime no site?']);

        EnigmaGameInteraction::factory()
            ->correct($responder)
            ->for($solved)
            ->create([
                'content' => 'Akiba',
            ]);

        EnigmaGame::factory()
            ->draft()
            ->for($author, 'author')
            ->create([
                'title' => 'Rascunho de teste',
                'content' => '/img/placeholders/default.webp',
                'solution' => 'Ainda nao publicada.',
            ]);

        EnigmaGame::factory()
            ->inactive()
            ->for($author, 'author')
            ->create([
                'title' => 'Enigma inativo',
                'content' => '/img/placeholders/default.webp',
            ]);
    }
}
